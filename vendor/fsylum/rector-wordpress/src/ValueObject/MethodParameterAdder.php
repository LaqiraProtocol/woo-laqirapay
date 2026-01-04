<?php

namespace Fsylum\RectorWordPress\ValueObject;

use PHPStan\Type\ObjectType;

final readonly class MethodParameterAdder
{
    public function __construct(private string $class, private string $method, private int $position, private mixed $value) {}

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getMethod(): string
    {
        return $this->method;
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
