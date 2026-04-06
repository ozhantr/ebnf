<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Lexer;

use Ozhantr\Ebnf\Diagnostic\SourcePosition;
use Ozhantr\Ebnf\Exception\LexicalException;
use Ozhantr\Ebnf\Token\Token;
use Ozhantr\Ebnf\Token\TokenType;

final class Lexer
{
    private int $offset = 0;
    private int $line = 1;
    private int $column = 1;
    private int $length;

    public function __construct(
        private readonly string $input,
    ) {
        $this->length = strlen($input);
    }

    /**
     * @return list<Token>
     */
    public function tokenize(): array
    {
        $tokens = [];

        while (($current = $this->peek()) !== null) {
            if ($this->isWhitespace($current)) {
                $this->advance();
                continue;
            }

            $line = $this->line;
            $column = $this->column;

            if ($current === '"') {
                $tokens[] = new Token(TokenType::STRING, $this->readString(), $line, $column);
                continue;
            }

            if ($current === '/') {
                $tokens[] = new Token(TokenType::REGEX, $this->readRegex(), $line, $column);
                continue;
            }

            if ($this->isIdentifierStart($current)) {
                $tokens[] = new Token(TokenType::IDENTIFIER, $this->readIdentifier(), $line, $column);
                continue;
            }

            $tokenType = match ($current) {
                '=' => TokenType::EQUALS,
                '|' => TokenType::PIPE,
                ',' => TokenType::COMMA,
                ';' => TokenType::SEMICOLON,
                '{' => TokenType::LBRACE,
                '}' => TokenType::RBRACE,
                '(' => TokenType::LPAREN,
                ')' => TokenType::RPAREN,
                '[' => TokenType::LBRACKET,
                ']' => TokenType::RBRACKET,
                default => null,
            };

            if ($tokenType === null) {
                throw LexicalException::unexpectedCharacter($current, new SourcePosition($line, $column));
            }

            $this->advance();
            $tokens[] = new Token($tokenType, $current, $line, $column);
        }

        $tokens[] = new Token(TokenType::EOF, null, $this->line, $this->column);

        return $tokens;
    }

    private function readIdentifier(): string
    {
        $value = '';

        while (($current = $this->peek()) !== null && $this->isIdentifierPart($current)) {
            $value .= $this->advance();
        }

        return $value;
    }

    private function readString(): string
    {
        $start = new SourcePosition($this->line, $this->column);
        $this->advance();

        $value = '';

        while (($current = $this->peek()) !== null) {
            if ($current === '"') {
                $this->advance();

                return $value;
            }

            if ($current === '\\') {
                $this->advance();
                $escaped = $this->advance();

                if ($escaped === null) {
                    throw LexicalException::unterminatedString($start);
                }

                $value .= match ($escaped) {
                    '"', '\\' => $escaped,
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    default => $escaped,
                };
                continue;
            }

            $value .= $this->advance();
        }

        throw LexicalException::unterminatedString($start);
    }

    private function readRegex(): string
    {
        $start = new SourcePosition($this->line, $this->column);
        $this->advance();

        $value = '';
        $escaped = false;

        while (($current = $this->peek()) !== null) {
            if (!$escaped && $current === '/') {
                $this->advance();

                return $value;
            }

            if (!$escaped && $current === "\n") {
                throw LexicalException::unterminatedString($start);
            }

            if ($current === '\\' && !$escaped) {
                $escaped = true;
                $value .= $this->advance();
                continue;
            }

            $escaped = false;
            $value .= $this->advance();
        }

        throw LexicalException::unterminatedString($start);
    }

    private function peek(): ?string
    {
        if ($this->offset >= $this->length) {
            return null;
        }

        return $this->input[$this->offset];
    }

    private function advance(): ?string
    {
        $character = $this->peek();

        if ($character === null) {
            return null;
        }

        $this->offset++;

        if ($character === "\n") {
            $this->line++;
            $this->column = 1;
        } else {
            $this->column++;
        }

        return $character;
    }

    private function isWhitespace(string $character): bool
    {
        return preg_match('/\s/u', $character) === 1;
    }

    private function isIdentifierStart(string $character): bool
    {
        return preg_match('/[A-Za-z_]/', $character) === 1;
    }

    private function isIdentifierPart(string $character): bool
    {
        return preg_match('/[A-Za-z0-9_-]/', $character) === 1;
    }
}
