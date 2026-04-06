<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Token;

use Ozhantr\Ebnf\Diagnostic\SourcePosition;

final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public ?string $value,
        public int $line,
        public int $column,
    ) {
    }

    public function position(): SourcePosition
    {
        return new SourcePosition($this->line, $this->column);
    }
}
