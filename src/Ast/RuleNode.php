<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Ast;

final readonly class RuleNode extends Node
{
    public function __construct(
        public string $name,
        public ExpressionNode $expression,
    ) {
    }
}
