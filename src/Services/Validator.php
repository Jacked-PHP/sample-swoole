<?php

namespace MyCode\Services;

use Exception;
use Symfony\Component\Validator\Validation;

class Validator
{
    /**
     * @param array $values
     * @param array $validationRules
     * @return void
     * @throws Exception
     */
    public static function validate(array $values, array $validationRules): void
    {
        $violations = [];

        $validator = Validation::createValidator();
        foreach ($values as $key => $value) {
            $tempViolations = $validator->validate($value, $validationRules[$key]);
            $violations[$key] = [];
            foreach ($tempViolations as $v) {
                $violations[$key][] = $v->getMessage();
            }
        }

        $errorMessage = '';
        foreach ($violations as $key => $violation) {
            if (count($violation) > 0) {
                array_map(function($v) use (&$errorMessage) {
                    $errorMessage .= '    - ' . $v . PHP_EOL;
                }, $violation);
            }
        }

        if (!empty($errorMessage)) {
            throw new Exception($errorMessage);
        }
    }
}