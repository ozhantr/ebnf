<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Parser;

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
use Ozhantr\Ebnf\Exception\UnexpectedTokenException;
use Ozhantr\Ebnf\Token\Token;
use Ozhantr\Ebnf\Token\TokenType;

final class Parser
{
    /** @var list<Token> */
    private array $tokens;
    private int $offset = 0;

    /**
     * @param iterable<Token> $tokens
     */
    public function __construct(iterable $tokens)
    {
        $this->tokens = is_array($tokens) ? array_values($tokens) : iterator_to_array($tokens, false);
    }

    public function parse(): GrammarNode
    {
        $rules = [];

        while (!$this->check(TokenType::EOF)) {
            $rules[] = $this->parseRule();
        }

        $this->consume(TokenType::EOF);

        return new GrammarNode($rules);
    }

    private function parseRule(): RuleNode
    {
        $name = $this->consume(TokenType::IDENTIFIER);
        $this->consume(TokenType::EQUALS);
        $expression = $this->parseChoice();
        $this->consume(TokenType::SEMICOLON);

        return new RuleNode((string) $name->value, $expression);
    }

    private function parseChoice(): ExpressionNode
    {
        $alternatives = [$this->parseSequence()];

        while ($this->match(TokenType::PIPE)) {
            $alternatives[] = $this->parseSequence();
        }

        if (count($alternatives) === 1) {
            return $alternatives[0];
        }

        return new ChoiceNode($alternatives);
    }

    private function parseSequence(): ExpressionNode
    {
        $elements = [$this->parsePrimary()];

        while ($this->match(TokenType::COMMA)) {
            $elements[] = $this->parsePrimary();
        }

        if (count($elements) === 1) {
            return $elements[0];
        }

        return new SequenceNode($elements);
    }

    private function parsePrimary(): ExpressionNode
    {
        if ($this->match(TokenType::STRING)) {
            return new TerminalNode((string) $this->previous()->value);
        }

        if ($this->match(TokenType::REGEX)) {
            return new RegexTerminalNode((string) $this->previous()->value);
        }

        if ($this->match(TokenType::IDENTIFIER)) {
            return new NonTerminalNode((string) $this->previous()->value);
        }

        if ($this->match(TokenType::LBRACKET)) {
            $expression = $this->parseChoice();
            $this->consume(TokenType::RBRACKET);

            return new OptionalNode($expression);
        }

        if ($this->match(TokenType::LBRACE)) {
            $expression = $this->parseChoice();
            $this->consume(TokenType::RBRACE);

            return new RepeatNode($expression);
        }

        if ($this->match(TokenType::LPAREN)) {
            $expression = $this->parseChoice();
            $this->consume(TokenType::RPAREN);

            return $expression;
        }

        throw UnexpectedTokenException::forToken(
            $this->current(),
            ['STRING', 'REGEX', 'IDENTIFIER', '[', '{', '(']
        );
    }

    private function consume(TokenType $type): Token
    {
        if ($this->check($type)) {
            return $this->advance();
        }

        throw UnexpectedTokenException::forToken($this->current(), [$type->name]);
    }

    private function match(TokenType ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                $this->advance();

                return true;
            }
        }

        return false;
    }

    private function check(TokenType $type): bool
    {
        return $this->current()->type === $type;
    }

    private function advance(): Token
    {
        $token = $this->current();

        if ($this->offset < count($this->tokens) - 1) {
            $this->offset++;
        }

        return $token;
    }

    private function current(): Token
    {
        return $this->tokens[$this->offset];
    }

    private function previous(): Token
    {
        return $this->tokens[max(0, $this->offset - 1)];
    }
}
