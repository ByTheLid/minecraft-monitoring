<?php

namespace App\Services;

use React\Socket\ConnectorInterface;
use React\Promise\PromiseInterface;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;

class AsyncMinecraftPing
{
    private ConnectorInterface $connector;
    private float $timeout;

    public function __construct(ConnectorInterface $connector, float $timeout = 5.0)
    {
        $this->connector = $connector;
        $this->timeout = $timeout;
    }

    public function ping(string $host, int $port = 25565): PromiseInterface
    {
        $deferred = new \React\Promise\Deferred();

        $uri = $host . ':' . $port;

        $this->connector->connect($uri)->then(
            function (ConnectionInterface $connection) use ($deferred, $host, $port) {
                $buffer = '';
                $startTime = microtime(true);
                $isResolved = false;

                // Send Handshake
                $handshake = $this->buildHandshake($host, $port);
                $connection->write($handshake);
                
                // Send Request
                $connection->write(pack('c', 1) . pack('c', 0));

                $connection->on('data', function ($chunk) use (&$buffer, $connection, $deferred, $startTime, &$isResolved) {
                    if ($isResolved) return;
                    $buffer .= $chunk;

                    // Try parsing the buffer
                    $parsed = $this->parseBuffer($buffer);
                    if ($parsed !== false) {
                        $isResolved = true;
                        $connection->close();
                        
                        $pingMs = (int)((microtime(true) - $startTime) * 1000);
                        
                        $json = json_decode($parsed, true);
                        if (!$json) {
                            $deferred->resolve($this->getDefaultResult());
                            return;
                        }

                        $result = parentResult($json, $pingMs);
                        $deferred->resolve($result);
                    }
                });

                $connection->on('error', function (\Exception $e) use ($deferred, $connection, &$isResolved) {
                    if (!$isResolved) {
                        $isResolved = true;
                        $deferred->resolve($this->getDefaultResult());
                        $connection->close();
                    }
                });

                $connection->on('close', function () use ($deferred, &$isResolved) {
                    if (!$isResolved) {
                        $isResolved = true;
                        $deferred->resolve($this->getDefaultResult());
                    }
                });

            },
            function (\Exception $e) use ($deferred) {
                // Connection failed
                $deferred->resolve($this->getDefaultResult());
            }
        );

        return \React\Promise\Timer\timeout($deferred->promise(), $this->timeout, \React\EventLoop\Loop::get())->then(
            null,
            function (\Exception $e) {
                return $this->getDefaultResult();
            }
        );
    }

    private function parseBuffer(string $buffer): string|false
    {
        $offset = 0;
        $length = $this->readVarInt($buffer, $offset);
        if ($length === false || strlen($buffer) < $offset + $length) {
            return false; // Need more data
        }

        $packetId = $this->readVarInt($buffer, $offset);
        if ($packetId === false || $packetId !== 0) {
            return false;
        }

        $jsonLength = $this->readVarInt($buffer, $offset);
        if ($jsonLength === false) {
            return false;
        }
        
        $json = substr($buffer, $offset, $jsonLength);
        if (strlen($json) < $jsonLength) {
            return false; // Need more data
        }

        return $json;
    }

    private function buildHandshake(string $host, int $port): string
    {
        $data = $this->writeVarInt(47); // Protocol version
        $data .= $this->writeVarInt(strlen($host)) . $host;
        $data .= pack('n', $port);
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

    private function readVarInt(string $buffer, int &$offset): int|false
    {
        $value = 0;
        $size = 0;

        do {
            if ($offset >= strlen($buffer)) {
                return false;
            }
            $byte = ord($buffer[$offset]);
            $offset++;
            
            $value |= ($byte & 0x7F) << ($size * 7);
            $size++;
            if ($size > 5) {
                return false;
            }
        } while (($byte & 0x80) !== 0);

        return $value;
    }

    private function getDefaultResult(): array
    {
        return [
            'is_online' => false,
            'players_online' => 0,
            'players_max' => 0,
            'version' => null,
            'ping_ms' => null,
            'motd' => null,
            'favicon' => null,
        ];
    }
}

function parentResult(array $json, int $pingMs): array
{
    $result = [
        'is_online' => true,
        'ping_ms' => $pingMs,
        'players_online' => $json['players']['online'] ?? 0,
        'players_max' => $json['players']['max'] ?? 0,
        'version' => $json['version']['name'] ?? null,
        'favicon' => $json['favicon'] ?? null,
    ];

    if (isset($json['description'])) {
        $result['motd'] = is_string($json['description'])
            ? $json['description']
            : ($json['description']['text'] ?? json_encode($json['description']));
    } else {
        $result['motd'] = null;
    }

    return $result;
}
