<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Exception;

use Ozhantr\Ebnf\Diagnostic\SourcePosition;

final class LexicalException extends SyntaxError
{
    public static function unexpectedCharacter(string $character, SourcePosition $position): self
    {
        return new self(sprintf('Unexpected character "%s"', $character), $position);
    }

    public static function unterminatedString(SourcePosition $position): self
    {
        return new self('Unterminated string literal', $position);
    }
}
