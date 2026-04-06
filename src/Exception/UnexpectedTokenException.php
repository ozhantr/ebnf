<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Exception;

use Ozhantr\Ebnf\Token\Token;

final class UnexpectedTokenException extends SyntaxError
{
    /**
     * @param list<string> $expected
     */
    public static function forToken(Token $actual, array $expected): self
    {
        $expectedList = implode(', ', $expected);

        return new self(
            sprintf(
                'Unexpected token %s (%s), expected one of: %s',
                $actual->type->name,
                $actual->value ?? 'null',
                $expectedList
            ),
            $actual->position()
        );
    }
}
