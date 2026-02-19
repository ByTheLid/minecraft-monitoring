<?php

namespace App\Services;

use Exception;

class VotifierService
{
    private string $serviceName;

    public function __construct(string $serviceName = 'MC-Monitor')
    {
        $this->serviceName = $serviceName;
    }

    /**
     * Send a vote to a Votifier server (V1 Protocol).
     *
     * @param string $host Server IP or hostname
     * @param int $port Votifier port (default 8192)
     * @param string $publicKey Public key (RSA)
     * @param string $username Minecraft username
     * @param string $address User IP address
     * @param int|null $timestamp Vote timestamp
     * @return bool True on success
     * @throws Exception If connection fails or encryption error
     */
    public function sendVote(string $host, int $port, string $publicKey, string $username, string $address, ?int $timestamp = null): bool
    {
        $timestamp = $timestamp ?? time();
        
        // Format public key
        $publicKey = $this->formatPublicKey($publicKey);

        // Connect to server
        $socket = @fsockopen($host, $port, $errno, $errstr, 3);
        if (!$socket) {
            throw new Exception("Connection failed: $errstr ($errno)");
        }

        // Read header
        $header = fread($socket, 64); // "VOTIFIER 1.9" etc.
        if (!$header) {
            fclose($socket);
            throw new Exception("Failed to read Votifier header");
        }

        // Prepare vote data
        // Format: "VOTE\nserviceName\nusername\naddress\ntimestamp\n"
        $voteData = "VOTE\n{$this->serviceName}\n{$username}\n{$address}\n{$timestamp}\n";

        // Encrypt with Public Key
        $encrypted = '';
        if (!openssl_public_encrypt($voteData, $encrypted, $publicKey)) {
            fclose($socket);
            throw new Exception("Encryption failed: " . openssl_error_string());
        }

        // Send encrypted data
        if (fwrite($socket, $encrypted) === false) {
             fclose($socket);
             throw new Exception("Failed to send data");
        }

        // Votifier protocol doesn't send a response after receiving data, just closes or waits.
        // We assume success if we wrote the data.
        
        fclose($socket);
        return true;
    }

    private function formatPublicKey(string $key): string
    {
        // Add headers if missing
        if (!str_contains($key, '-----BEGIN PUBLIC KEY-----')) {
            $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        }
        return $key;
    }
}
