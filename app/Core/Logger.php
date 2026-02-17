<?php

namespace App\Core;

class Logger
{
    private string $logDir;

    public function __construct(?string $logDir = null)
    {
        $this->logDir = $logDir ?? dirname(__DIR__, 2) . '/storage/logs';
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    private function log(string $level, string $message, array $context): void
    {
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $file = $this->logDir . "/{$date}.log";

        $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$time}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
