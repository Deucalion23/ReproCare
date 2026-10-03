<?php

namespace Tests\Feature;

use App\Services\AIInsightService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenRouterAnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        config([
            'cache.default' => 'array', 'services.analytics_ai.provider' => 'openrouter',
            'services.openrouter.api_key' => 'test-only-secret', 'services.openrouter.model' => 'meta-llama/llama-3.3-70b-instruct:free',
        ]);
    }

    private function report(): array
    {
        return [
            'filters' => ['from' => '2026-07-01', 'to' => '2026-09-15', 'barangay' => 'PrivateVillage'],
            'area_label' => 'PrivateVillage',
            'totals' => ['open' => 18, 'high_risk' => 8, 'emergency' => 0, 'registrations' => 18,
                'deaths' => 1, 'complications' => 4, 'pending_death_reviews' => 1,
                'care_gaps' => 7, 'past_due' => 0, 'unassessed' => 2],
            'risk_counts' => ['Critical' => 1, 'High' => 7, 'Medium' => 5, 'Low' => 3, 'Unassessed' => 2],
            'monthly' => [['label' => 'Jul 2026', 'registrations' => 18, 'deaths' => 1, 'complications' => 4]],
            'areas' => [['key' => 'PrivateVillage', 'label' => 'PrivateVillage', 'open' => 18,
                'high_risk' => 8, 'registrations' => 18, 'deaths' => 1, 'complications' => 4]],
            'queue' => [['name' => 'PrivatePatient', 'pregnancy_id' => 8765, 'contact' => '09179999999', 'notes' => 'private clinical notes']],
        ];
    }

    private function success(string $text = 'Review existing follow-up plans with the assigned team.'): array
    {
        return ['choices' => [['finish_reason' => 'stop', 'message' => ['content' => $text]]]];
    }

    public function test_provider_sends_the_question_but_excludes_registry_identifiers(): void
    {
        Http::fake(['https://openrouter.ai/api/v1/chat/completions' => Http::response($this->success())]);
        $result = app(AIInsightService::class)->chat('Which risks need follow-up?', $this->report());
        $this->assertSame('openrouter', $result['source']);
        $this->assertFalse($result['cached']);
        $this->assertStringContainsString('Draft answer', $result['notice']);
        Http::assertSent(function ($request) {
            $this->assertSame('https://openrouter.ai/api/v1/chat/completions', $request->url());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer test-only-secret'));
            $this->assertTrue($request->hasHeader('HTTP-Referer'));
            $this->assertTrue($request->hasHeader('X-Title'));
            foreach (['PrivatePatient', 'PrivateVillage', '09179999999', '8765', 'private clinical notes', '2026-07-01', 'Jul 2026'] as $private) {
                $this->assertStringNotContainsString($private, $request->body());
            }
            $data = json_decode($request['messages'][1]['content'], true);
            $this->assertSame('Which risks need follow-up?', $data['staff_question']);
            $this->assertArrayNotHasKey('queue', $data['report']);
            $this->assertFalse($request['stream']);
            $this->assertSame(2000, $request['max_tokens']);

            return true;
        });
        Http::assertSentCount(1);
    }

    public function test_free_suffix_models_are_accepted(): void
    {
        config(['services.openrouter.model' => 'not a model!!']);
        $answer = app(AIInsightService::class)->chat('summary', $this->report());
        $this->assertSame('rules', $answer['source']);
        $this->assertSame('configuration', $answer['error_code']);
        Http::assertNothingSent();
    }

    public function test_successful_answers_are_reused(): void
    {
        Http::fake(['*' => Http::response($this->success())]);
        $service = app(AIInsightService::class);
        $this->assertFalse($service->chat('summary', $this->report())['cached']);
        $this->assertTrue($service->chat('summary', $this->report())['cached']);
        Http::assertSentCount(1);
    }

    public function test_missing_key_falls_back_without_a_request(): void
    {
        config(['services.openrouter.api_key' => '']);
        $service = app(AIInsightService::class);
        $answer = $service->chat('summary', $this->report());
        $this->assertSame('rules', $answer['source']);
        $this->assertSame('missing_key', $answer['error_code']);
        $this->assertStringContainsString('OPENROUTER_API_KEY', $answer['notice']);
        $this->assertSame('OpenRouter needs setup', $service->status()['label']);
        Http::assertNothingSent();
    }

    public function test_rate_limit_uses_a_cooldown_and_preserves_local_answers(): void
    {
        Http::fake(['*' => Http::response(['error' => 'Secret provider message'], 429)]);
        $service = app(AIInsightService::class);
        foreach (['summary', 'maternal deaths'] as $question) {
            $answer = $service->chat($question, $this->report());
            $this->assertSame('rules', $answer['source']);
            $this->assertSame('rate_limited', $answer['error_code']);
            $this->assertStringNotContainsString('Secret provider message', json_encode($answer));
        }
        Http::assertSentCount(1);
    }

    public function test_provider_errors_are_actionable_and_do_not_expose_credentials(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['error' => 'test-only-secret'], 401)
            ->push(['error' => 'test-only-secret'], 404)
            ->push(['error' => 'test-only-secret'], 402)
            ->push(['error' => 'test-only-secret'], 500)
            ->push(['error' => 'test-only-secret'], 500)
            ->push([], 302, ['Location' => 'https://untrusted.example'])]);
        foreach (['authentication', 'model', 'billing', 'unavailable', 'unavailable'] as $expected) {
            $answer = app(AIInsightService::class)->chat('summary', $this->report());
            $this->assertSame('rules', $answer['source']);
            $this->assertSame($expected, $answer['error_code']);
            $this->assertStringNotContainsString('test-only-secret', json_encode($answer));
            $this->assertStringNotContainsString('untrusted.example', json_encode($answer));
        }
        Http::assertSentCount(6);
    }

    public function test_success_payload_with_provider_overload_error_retries_once_then_recovers(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['id' => 'gen-test', 'error' => ['message' => 'Upstream error from Nvidia: Service temporarily overloaded', 'code' => 503, 'metadata' => ['error_type' => 'provider_overloaded']]])
            ->push($this->success())]);
        $result = app(AIInsightService::class)->chat('summary', $this->report());
        $this->assertSame('openrouter', $result['source']);
        $this->assertFalse($result['cached']);
        Http::assertSentCount(2);
    }

    public function test_persistent_overload_reports_actionable_message_without_provider_details(): void
    {
        Http::fake(['*' => Http::response(['error' => 'test-only-secret'], 503)]);
        $answer = app(AIInsightService::class)->chat('summary', $this->report());
        $this->assertSame('rules', $answer['source']);
        $this->assertSame('unavailable', $answer['error_code']);
        $this->assertStringContainsString('overloaded', $answer['notice']);
        $this->assertStringNotContainsString('test-only-secret', json_encode($answer));
        Http::assertSentCount(2);
    }

    public function test_incomplete_answers_are_never_presented_as_ai_success_or_cached(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['choices' => [['finish_reason' => 'length', 'message' => ['content' => 'Partial draft']]]])
            ->push($this->success(''))
            ->push(['unexpected' => 'response'])
            ->push($this->success())]);
        $service = app(AIInsightService::class);
        for ($i = 0; $i < 3; $i++) {
            $result = $service->chat('summary', $this->report());
            $this->assertSame('rules', $result['source']);
            $this->assertSame('incomplete', $result['error_code']);
        }
        $this->assertSame('openrouter', $service->chat('summary', $this->report())['source']);
        Http::assertSentCount(4);
    }

    public function test_connection_failure_returns_local_answer_without_private_exception_text(): void
    {
        Http::fake(fn () => throw new ConnectionException('test-only-secret in request header'));
        $answer = app(AIInsightService::class)->chat('summary', $this->report());
        $this->assertSame('connection', $answer['error_code']);
        $this->assertSame('rules', $answer['source']);
        $this->assertStringNotContainsString('test-only-secret', json_encode($answer));
    }

    public function test_configuration_check_is_offline_and_hides_the_key(): void
    {
        $this->artisan('analytics:ai-check')->expectsOutput('OpenRouter API key: configured (hidden)')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_connection_check_uses_only_synthetic_data_without_a_database(): void
    {
        Http::fake(['*' => Http::response($this->success())]);
        $this->artisan('analytics:ai-check --connect')
            ->expectsOutput('OpenRouter connection succeeded. A complete AI response was received using synthetic data.')
            ->assertSuccessful();
        Http::assertSent(fn ($request) => str_contains($request['messages'][1]['content'], 'Synthetic connection test'));
        Http::assertSentCount(1);
    }
}
