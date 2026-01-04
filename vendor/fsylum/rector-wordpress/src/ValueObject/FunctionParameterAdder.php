<?php

namespace Fsylum\RectorWordPress\ValueObject;

final readonly class FunctionParameterAdder
{
    public function __construct(private string $function, private int $position, private mixed $value) {}

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
