<?php

namespace App\Helpers;

class Validator
{
    private array $errors = [];

    public function __construct(private array $data) {}

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (empty($this->data[$field]) && $this->data[$field] !== '0') {
            $this->errors[$field] = "$label is required";
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Invalid email address";
        }
        return $this;
    }

    public function min(string $field, int $min): self
    {
        $val = $this->data[$field] ?? '';
        if (strlen((string)$val) < $min) {
            $this->errors[$field] = "$field must be at least $min characters";
        }
        return $this;
    }

    public function max(string $field, int $max): self
    {
        $val = $this->data[$field] ?? '';
        if (strlen((string)$val) > $max) {
            $this->errors[$field] = "$field must be at most $max characters";
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$field must be a number";
        }
        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "$field must be one of: " . implode(', ', $allowed);
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validate(): void
    {
        if ($this->fails()) {
            Response::error('Validation failed', 422);
        }
    }
}
