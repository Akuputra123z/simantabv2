<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class VerificationSignatureTest extends TestCase
{
    public function test_invalid_or_expired_signed_url_redirects_gracefully_with_message(): void
    {
        // URL dengan signature ngawur / expired
        $invalidUrl = route('account.verify.execute', [
            'id' => 1,
            'hash' => 'dummyhash123',
            'expires' => now()->subMinute()->timestamp,
            'signature' => 'invalid_signature_xyz',
        ]);

        $response = $this->get($invalidUrl);

        $response->assertRedirect(route('account.verify.request'));
        $response->assertSessionHasErrors('identifier');
    }
}
