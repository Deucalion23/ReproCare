<?php

namespace Tests\Feature;

use App\Services\AIInsightService;
use App\Services\CloudAnalyticsContext;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqAnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        config([
            'cache.default' => 'array', 'services.analytics_ai.provider' => 'groq',
            'services.groq.api_key' => 'test-only-secret', 'services.groq.model' => 'qwen/qwen3.8-27b',
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

    public function test_real_online_provider_sends_the_question_but_excludes_registry_identifiers(): void
    {
        config(['services.groq.url' => 'https://untrusted.example']);
        Http::fake(['https://api.groq.com/openai/v1/chat/completions' => Http::response($this->success())]);
        $result = app(AIInsightService::class)->chat('Which risks need follow-up?', $this->report());
        $this->assertSame('groq', $result['source']);
        $this->assertFalse($result['cached']);
        $this->assertSame('PrivateVillage', $result['area_legend'][0]['label']);
        $this->assertStringContainsString('Online AI draft', $result['notice']);
        Http::assertSent(function ($request) {
            $this->assertSame('https://api.groq.com/openai/v1/chat/completions', $request->url());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer test-only-secret'));
            foreach (['PrivatePatient', 'PrivateVillage', '09179999999', '8765', 'private clinical notes', '2026-07-01', 'Jul 2026', 'Which risks affect'] as $private) {
                $this->assertStringNotContainsString($private, $request->body());
            }
            $data = json_decode($request['messages'][1]['content'], true);
            $this->assertSame('Which risks need follow-up?', $data['staff_question']);
            $this->assertSame('10-19', $data['report']['totals']['open']);
            $this->assertSame('Below 5 (includes zero)', $data['report']['totals']['deaths']);
            $this->assertSame('Area 1', $data['report']['areas'][0]['area']);
            $this->assertSame('Month 1', $data['report']['monthly'][0]['month']);
            $this->assertArrayNotHasKey('queue', $data['report']);
            $this->assertFalse($request['stream']);
            $this->assertSame(900, $request['max_completion_tokens']);

            return true;
        });
        Http::assertSentCount(1);
    }

    public function test_context_masks_small_counts_including_zero_and_rejects_unexpected_record_text(): void
    {
        $report = $this->report();
        $report['totals']['open'] = 'Injected private text';
        $report['totals']['extra_field'] = 'Another private value';
        $report['monthly'][0]['label'] = 'Private patient birthday';
        $prepared = app(CloudAnalyticsContext::class)->build($report);
        $context = $prepared['context'];
        $this->assertSame('Unavailable', $context['totals']['open']);
        $this->assertSame($context['totals']['deaths'], $context['totals']['emergency']);
        $this->assertSame($context['totals']['deaths'], $context['totals']['complications']);
        $this->assertStringNotContainsString('private', strtolower(json_encode($context)));
        $this->assertArrayNotHasKey('extra_field', $context['totals']);
    }

    public function test_successful_answers_are_reused_but_changed_grouped_data_invalidates_cache(): void
    {
        Http::fake(['*' => Http::response($this->success())]);
        $service = app(AIInsightService::class);
        $this->assertFalse($service->chat('summary', $this->report())['cached']);
        $this->assertTrue($service->chat('summary', $this->report())['cached']);
        Http::assertSentCount(1);
        $report = $this->report();
        $report['totals']['high_risk'] = 35;
        $this->assertFalse($service->chat('summary', $report)['cached']);
        Http::assertSentCount(2);
        $report['areas'][0]['label'] = 'New local label';
        $answer = $service->chat('summary', $report);
        $this->assertTrue($answer['cached']);
        $this->assertSame('New local label', $answer['area_legend'][0]['label']);
    }

    public function test_missing_key_falls_back_without_a_request_or_mislabeling_the_answer(): void
    {
        config(['services.groq.api_key' => '']);
        $service = app(AIInsightService::class);
        $answer = $service->chat('summary', $this->report());
        $this->assertSame('rules', $answer['source']);
        $this->assertSame('missing_key', $answer['error_code']);
        $this->assertStringContainsString('GROQ_API_KEY', $answer['notice']);
        $this->assertSame('Groq needs setup', $service->status()['label']);
        Http::assertNothingSent();
    }

    public function test_unsupported_topic_stays_local(): void
    {
        $answer = app(AIInsightService::class)->chat('Tell PrivatePatient what medicine to take', $this->report());
        $this->assertSame('rules', $answer['source']);
        $this->assertSame('unsupported_topic', $answer['error_code']);
        Http::assertNothingSent();
    }

    public function test_general_reproductive_questions_reach_ai_and_different_questions_do_not_share_cached_answers(): void
    {
        Http::fake(['https://api.groq.com/openai/v1/chat/completions' => Http::response($this->success('General education for staff review.'))]);
        foreach (['Explain breastfeeding support.', 'How does ReproCare help with family planning?'] as $question) {
            $answer = app(AIInsightService::class)->chat($question, $this->report());
            $this->assertSame('groq', $answer['source']);
            $this->assertFalse($answer['cached']);
        }
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'], 'outside my scope')
            && json_decode($r['messages'][1]['content'], true)['staff_question'] === 'Explain breastfeeding support.');
    }

    public function test_unrelated_questions_and_contact_details_stay_local(): void
    {
        $service = app(AIInsightService::class);
        $this->assertSame('out_of_scope', $service->chat('What is the weather?', $this->report())['error_code']);
        $this->assertSame('private_question', $service->chat('Review pregnancy risk at 09179999999', $this->report())['error_code']);
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

    public function test_provider_errors_are_actionable_and_do_not_expose_credentials_or_raw_bodies(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['error' => 'test-only-secret'], 401)
            ->push(['error' => 'test-only-secret'], 404)
            ->push(['error' => 'test-only-secret'], 500)
            ->push([], 302, ['Location' => 'https://untrusted.example'])]);
        foreach (['authentication', 'model', 'unavailable', 'unavailable'] as $expected) {
            $answer = app(AIInsightService::class)->chat('summary', $this->report());
            $this->assertSame('rules', $answer['source']);
            $this->assertSame($expected, $answer['error_code']);
            $this->assertStringNotContainsString('test-only-secret', json_encode($answer));
            $this->assertStringNotContainsString('untrusted.example', json_encode($answer));
        }
        Http::assertSentCount(4);
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
        $this->assertSame('groq', $service->chat('summary', $this->report())['source']);
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
        $this->artisan('analytics:ai-check')->expectsOutput('Groq API key: configured (hidden)')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_connection_check_uses_only_synthetic_data_without_a_database(): void
    {
        Http::fake(['*' => Http::response($this->success())]);
        $this->artisan('analytics:ai-check --connect')
            ->expectsOutput('Groq connection succeeded. A complete AI response was received using synthetic data.')
            ->assertSuccessful();
        Http::assertSent(fn ($request) => str_contains($request['messages'][1]['content'], 'Synthetic connection test'));
        Http::assertSentCount(1);
    }
}
