<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Ast;

final readonly class GrammarNode extends Node
{
    /**
     * @param list<RuleNode> $rules
     */
    public function __construct(
        public array $rules,
    ) {
    }
}
