<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Token;

enum TokenType: string
{
    case IDENTIFIER = 'IDENTIFIER';
    case STRING = 'STRING';
    case REGEX = 'REGEX';
    case EQUALS = '=';
    case PIPE = '|';
    case COMMA = ',';
    case SEMICOLON = ';';
    case LBRACE = '{';
    case RBRACE = '}';
    case LPAREN = '(';
    case RPAREN = ')';
    case LBRACKET = '[';
    case RBRACKET = ']';
    case EOF = 'EOF';
}
