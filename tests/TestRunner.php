<?php

namespace Tests;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "\033[32m.\033[0m"; // Green dot
        } else {
            $this->failed++;
            echo "\033[31mF\033[0m"; // Red F
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $this->failures[] = "[FAILED] " . $message . " at " . basename($trace['file']) . ":" . $trace['line'];
        }
    }

    public function assertEquals($expected, $actual, string $message = ''): void
    {
        $this->assert($expected === $actual, $message ?: "Expected " . print_r($expected, true) . ", got " . print_r($actual, true));
    }

    public function runDir(string $dir): void
    {
        $files = glob($dir . '/*Test.php');
        
        echo "Running tests...\n";
        
        foreach ($files as $file) {
            require_once $file;
            $className = 'Tests\\' . basename($file, '.php');
            if (class_exists($className)) {
                $testClass = new $className($this);
                $methods = get_class_methods($testClass);
                foreach ($methods as $method) {
                    if (str_starts_with($method, 'test')) {
                        try {
                            $testClass->$method();
                        } catch (\Throwable $e) {
                            $this->failed++;
                            echo "\033[31mE\033[0m"; // Red E for Error
                            $this->failures[] = "[ERROR] Exception in $className::$method: " . $e->getMessage();
                        }
                    }
                }
            }
        }

        echo "\n\n";
        
        if ($this->failed > 0) {
            echo "\033[31mFAILURES!\033[0m\n";
            echo "Passed: {$this->passed}, Failed: {$this->failed}\n\n";
            foreach ($this->failures as $fail) {
                echo $fail . "\n";
            }
            exit(1);
        } else {
            echo "\033[32mOK ({$this->passed} tests)\033[0m\n";
            exit(0);
        }
    }
}
