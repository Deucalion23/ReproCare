<?php

namespace Tests\Feature;

use Tests\TestCase;

class NoCacheResponsesTest extends TestCase
{
    public function test_public_pages_send_no_cache_headers(): void
    {
        $response = $this->get('/')->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');

        foreach (['no-store', 'no-cache', 'must-revalidate', 'max-age=0'] as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }
    }

    public function test_service_worker_uses_current_cache_version(): void
    {
        $contents = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString("const CACHE_VERSION = 'reprocare-v4';", $contents);
    }
}
