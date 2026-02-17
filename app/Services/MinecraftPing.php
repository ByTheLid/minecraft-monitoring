<?php

namespace App\Services;

class MinecraftPing
{
    private string $host;
    private int $port;
    private int $timeout;

    public function __construct(string $host, int $port = 25565, int $timeout = 5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function ping(): array
    {
        $result = [
            'is_online' => false,
            'players_online' => 0,
            'players_max' => 0,
            'version' => null,
            'ping_ms' => null,
            'motd' => null,
            'favicon' => null,
        ];

        try {
            $startTime = microtime(true);

            $socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if (!$socket) {
                return $result;
            }

            stream_set_timeout($socket, $this->timeout);

            // Build handshake packet
            $handshake = $this->buildHandshake();
            fwrite($socket, $handshake);

            // Send status request
            fwrite($socket, pack('c', 1) . pack('c', 0));

            // Read response
            $length = $this->readVarInt($socket);
            if ($length < 1) {
                fclose($socket);
                return $result;
            }

            $packetId = $this->readVarInt($socket);
            $jsonLength = $this->readVarInt($socket);

            if ($jsonLength < 1) {
                fclose($socket);
                return $result;
            }

            $data = '';
            $remaining = $jsonLength;
            while ($remaining > 0) {
                $chunk = fread($socket, min(8192, $remaining));
                if ($chunk === false || $chunk === '') {
                    break;
                }
                $data .= $chunk;
                $remaining -= strlen($chunk);
            }

            // Ping packet for latency
            $pingTime = microtime(true);
            $payload = pack('J', (int)(microtime(true) * 1000));
            fwrite($socket, pack('c', 9) . pack('c', 1) . $payload);
            @fread($socket, 10);
            $pongTime = microtime(true);

            fclose($socket);

            $json = json_decode($data, true);
            if (!$json) {
                return $result;
            }

            $result['is_online'] = true;
            $result['ping_ms'] = (int)(($pongTime - $pingTime) * 1000);
            $result['players_online'] = $json['players']['online'] ?? 0;
            $result['players_max'] = $json['players']['max'] ?? 0;

            // Version
            $result['version'] = $json['version']['name'] ?? null;

            // MOTD
            if (isset($json['description'])) {
                $result['motd'] = is_string($json['description'])
                    ? $json['description']
                    : ($json['description']['text'] ?? json_encode($json['description']));
            }

            // Favicon
            $result['favicon'] = $json['favicon'] ?? null;

        } catch (\Throwable $e) {
            logger()->error('Ping failed for ' . $this->host . ':' . $this->port . ' - ' . $e->getMessage());
        }

        return $result;
    }

    private function buildHandshake(): string
    {
        $data = $this->writeVarInt(47); // Protocol version
        $data .= $this->writeVarInt(strlen($this->host)) . $this->host;
        $data .= pack('n', $this->port);
        $data .= $this->writeVarInt(1); // Next state: status

        return $this->writeVarInt(strlen($data) + 1) . pack('c', 0) . $data;
    }

    private function writeVarInt(int $value): string
    {
        $result = '';
        do {
            $byte = $value & 0x7F;
            $value >>= 7;
            if ($value !== 0) {
                $byte |= 0x80;
            }
            $result .= chr($byte);
        } while ($value !== 0);
        return $result;
    }

    private function readVarInt($socket): int
    {
        $value = 0;
        $size = 0;

        do {
            $byte = @fread($socket, 1);
            if ($byte === false || $byte === '') {
                return -1;
            }
            $byte = ord($byte);
            $value |= ($byte & 0x7F) << ($size * 7);
            $size++;
            if ($size > 5) {
                return -1;
            }
        } while (($byte & 0x80) !== 0);

        return $value;
    }
}
