<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Analyzer;

use Ozhantr\Ebnf\Ast\ChoiceNode;
use Ozhantr\Ebnf\Ast\ExpressionNode;
use Ozhantr\Ebnf\Ast\GrammarNode;
use Ozhantr\Ebnf\Ast\NonTerminalNode;
use Ozhantr\Ebnf\Ast\OptionalNode;
use Ozhantr\Ebnf\Ast\RegexTerminalNode;
use Ozhantr\Ebnf\Ast\RepeatNode;
use Ozhantr\Ebnf\Ast\SequenceNode;
use Ozhantr\Ebnf\Ast\TerminalNode;
use Ozhantr\Ebnf\Diagnostic\Diagnostic;
use Ozhantr\Ebnf\Diagnostic\DiagnosticSeverity;

final class GrammarAnalyzer
{
    public function analyze(GrammarNode $grammar): AnalysisResult
    {
        $diagnostics = [];
        $definedRules = [];
        $references = [];

        foreach ($grammar->rules as $index => $rule) {
            if (isset($definedRules[$rule->name])) {
                $diagnostics[] = new Diagnostic(
                    DiagnosticSeverity::ERROR,
                    sprintf('Duplicate rule "%s". First declared at index %d.', $rule->name, $definedRules[$rule->name]),
                    $rule->name
                );
            } else {
                $definedRules[$rule->name] = $index;
            }

            $references[$rule->name] = $this->collectReferences($rule->expression);
        }

        foreach ($references as $ruleName => $ruleReferences) {
            foreach ($ruleReferences as $reference) {
                if (!array_key_exists($reference, $definedRules)) {
                    $diagnostics[] = new Diagnostic(
                        DiagnosticSeverity::ERROR,
                        sprintf('Rule "%s" references undefined rule "%s".', $ruleName, $reference),
                        $ruleName
                    );
                }
            }
        }

        $entryRule = $grammar->rules === []
            ? null
            : $grammar->rules[array_key_last($grammar->rules)]->name;

        if ($entryRule !== null) {
            $reachable = $this->collectReachableRules($entryRule, $references);

            foreach (array_keys($definedRules) as $ruleName) {
                if (!in_array($ruleName, $reachable, true)) {
                    $diagnostics[] = new Diagnostic(
                        DiagnosticSeverity::WARNING,
                        sprintf('Rule "%s" is unreachable from entry rule "%s".', $ruleName, $entryRule),
                        $ruleName
                    );
                }
            }
        }

        return new AnalysisResult($diagnostics);
    }

    /**
     * @return list<string>
     */
    private function collectReferences(ExpressionNode $expression): array
    {
        return match (true) {
            $expression instanceof NonTerminalNode => [$expression->name],
            $expression instanceof SequenceNode => $this->collectNestedReferences($expression->elements),
            $expression instanceof ChoiceNode => $this->collectNestedReferences($expression->alternatives),
            $expression instanceof OptionalNode => $this->collectReferences($expression->expression),
            $expression instanceof RepeatNode => $this->collectReferences($expression->expression),
            $expression instanceof TerminalNode, $expression instanceof RegexTerminalNode => [],
            default => [],
        };
    }

    /**
     * @param list<ExpressionNode> $nodes
     * @return list<string>
     */
    private function collectNestedReferences(array $nodes): array
    {
        $references = [];

        foreach ($nodes as $node) {
            $references = array_merge($references, $this->collectReferences($node));
        }

        return array_values(array_unique($references));
    }

    /**
     * @param array<string, list<string>> $references
     * @return list<string>
     */
    private function collectReachableRules(string $entryRule, array $references): array
    {
        $visited = [];
        /** @var list<string> $stack */
        $stack = [$entryRule];

        while ($stack !== []) {
            $current = array_pop($stack);

            if (in_array($current, $visited, true)) {
                continue;
            }

            $visited[] = $current;

            foreach ($references[$current] ?? [] as $reference) {
                $stack[] = $reference;
            }
        }

        return $visited;
    }
}
