<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

class ValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function test_required_rule()
    {
        $rules = ['name' => 'required'];
        
        $this->assertTrue($this->validator->validate(['name' => 'John'], $rules));
        $this->assertFalse($this->validator->validate(['name' => ''], $rules));
        $this->assertFalse($this->validator->validate(['name' => null], $rules));
    }

    public function test_email_rule()
    {
        $rules = ['email' => 'email'];

        $this->assertTrue($this->validator->validate(['email' => 'test@example.com'], $rules));
        $this->assertFalse($this->validator->validate(['email' => 'invalid-email'], $rules));
    }

    public function test_min_max_rule()
    {
        $rules = ['username' => 'min:3|max:5'];

        $this->assertTrue($this->validator->validate(['username' => 'user'], $rules)); // 4 chars
        $this->assertFalse($this->validator->validate(['username' => 'us'], $rules)); // 2 chars
        $this->assertFalse($this->validator->validate(['username' => 'toolong'], $rules)); // 7 chars
    }

    public function test_numeric_rule()
    {
        $rules = ['age' => 'numeric'];

        $this->assertTrue($this->validator->validate(['age' => 25], $rules));
        $this->assertTrue($this->validator->validate(['age' => '25'], $rules));
        $this->assertFalse($this->validator->validate(['age' => 'abc'], $rules));
    }

    public function test_between_rule()
    {
        $rules = ['score' => 'between:1,10'];
        
        $this->assertTrue($this->validator->validate(['score' => 5], $rules));
        $this->assertTrue($this->validator->validate(['score' => 1], $rules));
        $this->assertTrue($this->validator->validate(['score' => 10], $rules));
        $this->assertFalse($this->validator->validate(['score' => 0], $rules));
        $this->assertFalse($this->validator->validate(['score' => 11], $rules));
    }
    
    public function test_errors_array()
    {
        $rules = ['name' => 'required'];
        $this->validator->validate([], $rules);
        
        $this->assertArrayHasKey('name', $this->validator->errors());
        $this->assertEquals('Field is required', $this->validator->errors()['name'][0]);
    }
}
