<?php

namespace App\Core;

class Validator
{
    protected array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            
            foreach ($rulesArray as $rule) {
                $params = [];
                
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $value = $data[$field] ?? null;

                // Helper to check if value is present (including 0)
                $hasValue = $value !== null && $value !== '';

                if ($rule === 'required') {
                    if (!$hasValue) {
                        $this->addError($field, 'required', 'Field is required');
                    }
                } elseif ($rule === 'email') {
                    if ($hasValue && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                         $this->addError($field, 'email', 'Invalid email format');
                    }
                } elseif ($rule === 'min') {
                    $min = (int) ($params[0] ?? 0);
                    if ($hasValue && strlen((string)$value) < $min) {
                        $this->addError($field, 'min', "Must be at least {$min} characters");
                    }
                } elseif ($rule === 'max') {
                    $max = (int) ($params[0] ?? 0);
                    if ($hasValue && strlen((string)$value) > $max) {
                        $this->addError($field, 'max', "Must be at most {$max} characters");
                    }
                } elseif ($rule === 'numeric') {
                    if ($hasValue && !is_numeric($value)) {
                        $this->addError($field, 'numeric', "Must be a number");
                    }
                } elseif ($rule === 'between') {
                    $min = (int) ($params[0] ?? 0);
                    $max = (int) ($params[1] ?? 0);
                    if ($hasValue && ($value < $min || $value > $max)) {
                        $this->addError($field, 'between', "Must be between {$min} and {$max}");
                    }
                } elseif ($rule === 'regex') {
                    if ($hasValue && !preg_match($params[0], $value)) {
                        $this->addError($field, 'regex', "Invalid format");
                    }
                } elseif ($rule === 'url') {
                    if ($hasValue && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $this->addError($field, 'url', "Invalid URL");
                    }
                } elseif ($rule === 'ip') {
                    if ($hasValue) {
                         // Check for valid IP
                         $isIp = filter_var($value, FILTER_VALIDATE_IP);
                         // Or domain name regex (basic)
                         $isDomain = preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/', $value);
                         
                         if (!$isIp && !$isDomain && $value !== 'localhost') {
                             $this->addError($field, 'ip', "Invalid IP address or domain");
                         }
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
    
    public function firstError(): ?string
    {
        $first = reset($this->errors);
        return $first ? reset($first) : null;
    }

    protected function addError(string $field, string $rule, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
}
