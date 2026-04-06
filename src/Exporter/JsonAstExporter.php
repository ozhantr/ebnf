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

final class JsonAstExporter
{
    public function export(GrammarNode $grammar, int $flags = JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR): string
    {
        return json_encode($this->normalizeGrammar($grammar), $flags);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeGrammar(GrammarNode $grammar): array
    {
        return [
            'type' => 'grammar',
            'rules' => array_map($this->normalizeRule(...), $grammar->rules),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRule(RuleNode $rule): array
    {
        return [
            'type' => 'rule',
            'name' => $rule->name,
            'expression' => $this->normalizeExpression($rule->expression),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeExpression(ExpressionNode $expression): array
    {
        return match (true) {
            $expression instanceof ChoiceNode => [
                'type' => 'choice',
                'alternatives' => array_map($this->normalizeExpression(...), $expression->alternatives),
            ],
            $expression instanceof SequenceNode => [
                'type' => 'sequence',
                'elements' => array_map($this->normalizeExpression(...), $expression->elements),
            ],
            $expression instanceof OptionalNode => [
                'type' => 'optional',
                'expression' => $this->normalizeExpression($expression->expression),
            ],
            $expression instanceof RepeatNode => [
                'type' => 'repeat',
                'expression' => $this->normalizeExpression($expression->expression),
            ],
            $expression instanceof TerminalNode => [
                'type' => 'terminal',
                'value' => $expression->value,
            ],
            $expression instanceof RegexTerminalNode => [
                'type' => 'regex_terminal',
                'pattern' => $expression->pattern,
            ],
            $expression instanceof NonTerminalNode => [
                'type' => 'non_terminal',
                'name' => $expression->name,
            ],
            default => [
                'type' => 'unknown',
            ],
        };
    }
}
