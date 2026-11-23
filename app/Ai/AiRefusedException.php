<?php

namespace App\Ai;

class AiRefusedException extends AiException
{
    public function __construct(string $message, public readonly array $usage = ['input' => 0, 'output' => 0], public readonly string $model = '')
    {
        parent::__construct($message);
    }
}
