<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\VotifierService;

class VotifierServiceTest extends TestCase
{
    private $service;
    private $keyPair;

    protected function setUp(): void
    {
        $this->service = new VotifierService('TestService');
        
        // Generate a new key pair for testing
        $this->keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
    }

    public function test_format_public_key()
    {
        $details = openssl_pkey_get_details($this->keyPair);
        $fullKey = $details['key'];
        
        // Strip headers to simulate raw key from DB
        $rawKey = str_replace(
            ["-----BEGIN PUBLIC KEY-----\n", "\n-----END PUBLIC KEY-----", "\n"], 
            '', 
            $fullKey
        );

        // Access private method via reflection
        $reflection = new \ReflectionClass(VotifierService::class);
        $method = $reflection->getMethod('formatPublicKey');
        $method->setAccessible(true);
        
        $formatted = $method->invoke($this->service, $rawKey);
        
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $formatted);
        $this->assertStringContainsString('-----END PUBLIC KEY-----', $formatted);
        // Normalize line endings and whitespace for comparison? 
        // Or just check if openssl can read it
        $this->assertNotFalse(openssl_pkey_get_public($formatted));
    }
    
    // We cannot easily test socket connection in a unit test without a running mock server.
    // So we skip the actual sendVote test or mock fsockopen if possible (namespace overriding).
    // For now, let's test only the encryption/formatting logic parts if separated.
}
