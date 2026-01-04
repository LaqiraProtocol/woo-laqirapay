<?php

namespace Fsylum\RectorWordPress\ValueObject;

final readonly class FunctionParameterPrepender
{
    public function __construct(private string $function, private mixed $value) {}

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
