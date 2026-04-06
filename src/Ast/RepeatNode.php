<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Ast;

final readonly class RepeatNode extends ExpressionNode
{
    public function __construct(
        public ExpressionNode $expression,
    ) {
    }
}
