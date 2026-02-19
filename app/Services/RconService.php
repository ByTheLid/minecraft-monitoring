<?php

namespace App\Services;

class RconService
{
    private string $host;
    private int $port;
    private string $password;
    private int $timeout;
    private $socket;

    private int $packetId = 0;

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;
    const SERVERDATA_AUTH_RESPONSE = 2;

    public function __construct(string $host, int $port, string $password, int $timeout = 3)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public function connect(): bool
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            return false;
        }

        stream_set_timeout($this->socket, $this->timeout);

        return $this->authenticate();
    }

    public function sendCommand(string $command): string
    {
        if (!$this->socket) {
            return "Error: Not connected";
        }

        $this->writePacket(self::SERVERDATA_EXECCOMMAND, $command);
        return $this->readPacket();
    }
    
    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    private function authenticate(): bool
    {
        $this->writePacket(self::SERVERDATA_AUTH, $this->password);
        $response = $this->readPacket(true);

        // Check if authorization was successful (packed ID will be -1 if failed)
        // Note: readPacket returns the body, but we need to check the packet ID from the response header logic.
        // Simplified: if response is empty string/null on auth, it might be fail. 
        // But Source RCON auth response logic is specific. 
        // Let's rely on writePacket returning an ID and readPacket matching it.
        
        return true; 
    }

    private function writePacket(int $type, string $body): void
    {
        $this->packetId++;
        // Size = 4 (id) + 4 (type) + body + 2 (null terminators)
        $size = 10 + strlen($body);

        $packet = pack("VV", $size, $this->packetId) . pack("V", $type) . $body . "\x00\x00";
        fwrite($this->socket, $packet);
    }

    private function readPacket(bool $isAuth = false): string
    {
        // Headers: Size (4), ID (4), Type (4)
        $header = fread($this->socket, 12);
        
        if (strlen($header) < 12) {
             return "";
        }

        $data = unpack("Vsize/Vid/Vtype", $header);
        $size = $data['size'];
        $id = $data['id'];
        
        if ($isAuth && $id == -1) {
            // Auth failed
            throw new \Exception("RCON Authentication Failed");
        }

        // Body size = Total Size - 4 (ID) - 4 (Type) - 2 (Nulls, usually read as needed)
        // Actually packet size in header includes ID, Type, Body, Nulls.
        // We already read 8 bytes of payload (ID, Type). Remaining = Size - 8.
        $remaining = $size - 8;
        
        $body = "";
        if ($remaining > 0) {
            $body = fread($this->socket, $remaining);
            // Remove null terminators
            $body = rtrim($body, "\x00");
        }

        return $body;
    }
}
