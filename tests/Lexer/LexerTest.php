<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Tests\Lexer;

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Token\TokenType;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    public function testItTokenizesBasicGrammar(): void
    {
        $lexer = new Lexer('digit = "0" | "1" ;');
        $tokens = $lexer->tokenize();

        self::assertSame(
            [
                TokenType::IDENTIFIER,
                TokenType::EQUALS,
                TokenType::STRING,
                TokenType::PIPE,
                TokenType::STRING,
                TokenType::SEMICOLON,
                TokenType::EOF,
            ],
            array_map(static fn ($token) => $token->type, $tokens)
        );

        self::assertSame('digit', $tokens[0]->value);
        self::assertSame('0', $tokens[2]->value);
        self::assertSame('1', $tokens[4]->value);
    }

    public function testItTokenizesRegexTerminals(): void
    {
        $lexer = new Lexer('attribut = /^[a-z][a-z0-9]*/ ;');
        $tokens = $lexer->tokenize();

        self::assertSame(TokenType::REGEX, $tokens[2]->type);
        self::assertSame('^[a-z][a-z0-9]*', $tokens[2]->value);
    }
}
