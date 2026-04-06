<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Exporter;

use Ozhantr\Ebnf\Ast\ChoiceNode;
use Ozhantr\Ebnf\Ast\ExpressionNode;
use Ozhantr\Ebnf\Ast\GrammarNode;
use Ozhantr\Ebnf\Ast\NonTerminalNode;
use Ozhantr\Ebnf\Ast\OptionalNode;
use Ozhantr\Ebnf\Ast\RegexTerminalNode;
use Ozhantr\Ebnf\Ast\RepeatNode;
use Ozhantr\Ebnf\Ast\RuleNode;
use Ozhantr\Ebnf\Ast\SequenceNode;
use Ozhantr\Ebnf\Ast\TerminalNode;

final class PrettyPrinter
{
    public function print(GrammarNode $grammar): string
    {
        return implode(
            PHP_EOL,
            array_map(fn (RuleNode $rule): string => $this->printRule($rule), $grammar->rules)
        );
    }

    private function printRule(RuleNode $rule): string
    {
        return sprintf('%s = %s ;', $rule->name, $this->printExpression($rule->expression));
    }

    private function printExpression(ExpressionNode $expression): string
    {
        return match (true) {
            $expression instanceof ChoiceNode => implode(
                ' | ',
                array_map(fn (ExpressionNode $node): string => $this->printExpression($node), $expression->alternatives)
            ),
            $expression instanceof SequenceNode => implode(
                ' , ',
                array_map(fn (ExpressionNode $node): string => $this->printExpression($node), $expression->elements)
            ),
            $expression instanceof OptionalNode => sprintf('[ %s ]', $this->printExpression($expression->expression)),
            $expression instanceof RepeatNode => sprintf('{ %s }', $this->printExpression($expression->expression)),
            $expression instanceof TerminalNode => sprintf('"%s"', addcslashes($expression->value, "\"\\")),
            $expression instanceof RegexTerminalNode => sprintf('/%s/', $expression->pattern),
            $expression instanceof NonTerminalNode => $expression->name,
            default => '',
        };
    }
}
