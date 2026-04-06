<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Ast;

final readonly class NonTerminalNode extends ExpressionNode
{
    public function __construct(
        public string $name,
    ) {
    }
}
