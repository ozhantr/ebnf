<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Diagnostic;

final readonly class SourcePosition
{
    public function __construct(
        public int $line,
        public int $column,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('line %d, column %d', $this->line, $this->column);
    }
}
