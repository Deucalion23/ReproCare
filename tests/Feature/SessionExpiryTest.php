<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class SessionExpiryTest extends TestCase
{
    public function test_expired_session_renders_login_redirect_with_message(): void
    {
        $request = Request::create('/auth/login', 'POST');
        $request->setLaravelSession(app('session')->driver());

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertTrue($response->isRedirect(route('login')));
        $this->assertSame(
            'Your session expired. Please log in again.',
            $response->getSession()->get('errors')->first('session')
        );
    }

    public function test_expired_ajax_session_renders_json_419(): void
    {
        $request = Request::create('/auth/login', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X-REQUESTED-WITH' => 'XMLHttpRequest',
        ]);

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame(
            'Session expired. Please refresh and try again.',
            $response->getData(true)['message']
        );
    }
}
