<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Runtime;

final readonly class RuntimeState
{
    private function __construct(
        public bool $success,
        public int $offset,
        public ?MatchError $error = null,
    ) {
    }

    public static function success(int $offset): self
    {
        return new self(true, $offset);
    }

    public static function failure(int $offset, MatchError $error): self
    {
        return new self(false, $offset, $error);
    }
}
