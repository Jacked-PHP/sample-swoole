<?php

namespace MyCode\Rules;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RecordExist extends Constraint
{
    public $message = 'The record doesn\'t exists at "{{ model }}".';
    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties

    public $input;

    public function __construct(
        array $input,
        string $message = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        $options['input'] = $input;
        parent::__construct($options, $groups, $payload);
        $this->message = $message ?? $this->message;
        $this->input = $input;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'input';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['input'];
    }
}