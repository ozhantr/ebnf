<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Runtime;

final readonly class MatchResult
{
    public function __construct(
        public bool $success,
        public int $consumedLength,
        public ?MatchError $error = null,
        public ?string $rule = null,
    ) {
    }
}
