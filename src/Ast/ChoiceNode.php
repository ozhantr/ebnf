<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Ast;

final readonly class ChoiceNode extends ExpressionNode
{
    /**
     * @param list<ExpressionNode> $alternatives
     */
    public function __construct(
        public array $alternatives,
    ) {
    }
}
