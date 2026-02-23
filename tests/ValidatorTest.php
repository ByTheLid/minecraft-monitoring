<?php

namespace Tests;

use App\Core\Validator;

class ValidatorTest
{
    private TestRunner $runner;

    public function __construct(TestRunner $runner)
    {
        $this->runner = $runner;
    }

    public function testRequiredRule()
    {
        $v = new Validator();
        
        $dataPass = ['name' => 'John'];
        $dataFail = ['name' => ''];
        $dataFailNull = [];

        $rules = ['name' => 'required'];

        $this->runner->assert($v->validate($dataPass, $rules) === true, "Required rule should pass when data exists");
        $this->runner->assert($v->validate($dataFail, $rules) === false, "Required rule should fail when string is empty");
        $this->runner->assert($v->validate($dataFailNull, $rules) === false, "Required rule should fail when key is missing");
    }

    public function testEmailRule()
    {
        $v = new Validator();
        
        $rules = ['email' => 'email'];

        $this->runner->assert($v->validate(['email' => 'test@example.com'], $rules) === true, "Valid email should pass");
        $this->runner->assert($v->validate(['email' => 'invalid-email'], $rules) === false, "Invalid email should fail");
    }

    public function testMinMaxRules()
    {
        $v = new Validator();
        
        $rules = [
            'username' => 'min:3|max:10'
        ];

        $this->runner->assert($v->validate(['username' => 'usr'], $rules) === true, "Min 3 should pass for 3 chars");
        $this->runner->assert($v->validate(['username' => 'us'], $rules) === false, "Min 3 should fail for 2 chars");
        $this->runner->assert($v->validate(['username' => '1234567890'], $rules) === true, "Max 10 should pass for 10 chars");
        $this->runner->assert($v->validate(['username' => '12345678901'], $rules) === false, "Max 10 should fail for 11 chars");
    }
    
    public function testNumericRule()
    {
        $v = new Validator();
        
        $rules = ['age' => 'numeric'];

        $this->runner->assert($v->validate(['age' => '25'], $rules) === true, "Numeric string should pass");
        $this->runner->assert($v->validate(['age' => 25], $rules) === true, "Integer should pass");
        $this->runner->assert($v->validate(['age' => '25a'], $rules) === false, "Non-numeric string should fail");
    }
}
