<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Runtime;

use InvalidArgumentException;
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

final class GrammarRuntime
{
    /** @var array<string, RuleNode> */
    private array $rules = [];

    public function __construct(GrammarNode $grammar)
    {
        foreach ($grammar->rules as $rule) {
            $this->rules[$rule->name] = $rule;
        }
    }

    public function match(string $ruleName, string $input): MatchResult
    {
        $rule = $this->rules[$ruleName] ?? null;

        if ($rule === null) {
            throw new InvalidArgumentException(sprintf('Unknown rule "%s".', $ruleName));
        }

        $state = $this->matchExpression($rule->expression, $input, 0, $ruleName);

        if (!$state->success) {
            return new MatchResult(false, $state->offset, $state->error, $ruleName);
        }

        if ($state->offset !== strlen($input)) {
            $error = $this->errorAt($input, $state->offset, ['EOF'], $ruleName);

            return new MatchResult(false, $state->offset, $error, $ruleName);
        }

        return new MatchResult(true, $state->offset, null, $ruleName);
    }

    private function matchExpression(ExpressionNode $node, string $input, int $offset, ?string $ruleName = null): RuntimeState
    {
        return match (true) {
            $node instanceof TerminalNode => $this->matchTerminal($node, $input, $offset, $ruleName),
            $node instanceof RegexTerminalNode => $this->matchRegex($node, $input, $offset, $ruleName),
            $node instanceof SequenceNode => $this->matchSequence($node, $input, $offset, $ruleName),
            $node instanceof ChoiceNode => $this->matchChoice($node, $input, $offset, $ruleName),
            $node instanceof OptionalNode => $this->matchOptional($node, $input, $offset, $ruleName),
            $node instanceof RepeatNode => $this->matchRepeat($node, $input, $offset, $ruleName),
            $node instanceof NonTerminalNode => $this->matchNonTerminal($node, $input, $offset),
            default => RuntimeState::failure($offset, $this->errorAt($input, $offset, ['expression'], $ruleName)),
        };
    }

    private function matchTerminal(TerminalNode $node, string $input, int $offset, ?string $ruleName): RuntimeState
    {
        if (substr($input, $offset, strlen($node->value)) === $node->value) {
            return RuntimeState::success($offset + strlen($node->value));
        }

        return RuntimeState::failure(
            $offset,
            $this->errorAt($input, $offset, [sprintf('"%s"', $node->value)], $ruleName)
        );
    }

    private function matchRegex(RegexTerminalNode $node, string $input, int $offset, ?string $ruleName): RuntimeState
    {
        $subject = substr($input, $offset);
        $pattern = $this->buildAnchoredPattern($node->pattern);

        $matched = preg_match($pattern, $subject, $matches);

        if ($matched === false) {
            throw new InvalidArgumentException(sprintf('Invalid regex pattern "%s".', $node->pattern));
        }

        if ($matched === 1) {
            return RuntimeState::success($offset + strlen($matches[0]));
        }

        return RuntimeState::failure(
            $offset,
            $this->errorAt($input, $offset, [sprintf('/%s/', $node->pattern)], $ruleName)
        );
    }

    private function matchSequence(SequenceNode $node, string $input, int $offset, ?string $ruleName): RuntimeState
    {
        $currentOffset = $offset;

        foreach ($node->elements as $element) {
            $state = $this->matchExpression($element, $input, $currentOffset, $ruleName);

            if (!$state->success) {
                return $state;
            }

            $currentOffset = $state->offset;
        }

        return RuntimeState::success($currentOffset);
    }

    private function matchChoice(ChoiceNode $node, string $input, int $offset, ?string $ruleName): RuntimeState
    {
        $bestError = null;

        foreach ($node->alternatives as $alternative) {
            $state = $this->matchExpression($alternative, $input, $offset, $ruleName);

            if ($state->success) {
                return $state;
            }

            $bestError = $this->mergeErrors($bestError, $state->error);
        }

        return RuntimeState::failure($offset, $bestError ?? $this->errorAt($input, $offset, ['alternative'], $ruleName));
    }

    private function matchOptional(OptionalNode $node, string $input, int $offset, ?string $ruleName): RuntimeState
    {
        $state = $this->matchExpression($node->expression, $input, $offset, $ruleName);

        if ($state->success) {
            return $state;
        }

        return RuntimeState::success($offset);
    }

    private function matchRepeat(RepeatNode $node, string $input, int $offset, ?string $ruleName): RuntimeState
    {
        $currentOffset = $offset;

        while (true) {
            $state = $this->matchExpression($node->expression, $input, $currentOffset, $ruleName);

            if (!$state->success) {
                if ($state->offset > $currentOffset) {
                    return $state;
                }

                return RuntimeState::success($currentOffset);
            }

            if ($state->offset === $currentOffset) {
                return RuntimeState::success($currentOffset);
            }

            $currentOffset = $state->offset;
        }
    }

    private function matchNonTerminal(NonTerminalNode $node, string $input, int $offset): RuntimeState
    {
        $rule = $this->rules[$node->name] ?? null;

        if ($rule === null) {
            return RuntimeState::failure(
                $offset,
                $this->errorAt($input, $offset, [$node->name], $node->name)
            );
        }

        return $this->matchExpression($rule->expression, $input, $offset, $node->name);
    }

    private function errorAt(string $input, int $offset, array $expected, ?string $ruleName): MatchError
    {
        [$line, $column] = $this->lineAndColumnForOffset($input, $offset);

        return new MatchError(
            $offset,
            $line,
            $column,
            array_values(array_unique($expected)),
            $offset >= strlen($input) ? null : $input[$offset],
            $ruleName
        );
    }

    private function mergeErrors(?MatchError $left, ?MatchError $right): ?MatchError
    {
        if ($left === null) {
            return $right;
        }

        if ($right === null) {
            return $left;
        }

        if ($right->offset > $left->offset) {
            return $right;
        }

        if ($right->offset < $left->offset) {
            return $left;
        }

        return new MatchError(
            $left->offset,
            $left->line,
            $left->column,
            array_values(array_unique(array_merge($left->expected, $right->expected))),
            $left->found,
            $left->rule
        );
    }

    /**
     * @return array{int, int}
     */
    private function lineAndColumnForOffset(string $input, int $offset): array
    {
        $line = 1;
        $column = 1;
        $length = min($offset, strlen($input));

        for ($index = 0; $index < $length; $index++) {
            if ($input[$index] === "\n") {
                $line++;
                $column = 1;
                continue;
            }

            $column++;
        }

        return [$line, $column];
    }

    private function buildAnchoredPattern(string $pattern): string
    {
        $delimiter = $this->pickDelimiter($pattern);

        return sprintf('%s(?:%s)%sA', $delimiter, str_replace($delimiter, '\\' . $delimiter, $pattern), $delimiter);
    }

    private function pickDelimiter(string $pattern): string
    {
        foreach (['~', '#', '%', '!'] as $delimiter) {
            if (!str_contains($pattern, $delimiter)) {
                return $delimiter;
            }
        }

        return '/';
    }
}
