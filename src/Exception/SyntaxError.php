<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Exception;

use Ozhantr\Ebnf\Diagnostic\SourcePosition;

class SyntaxError extends EbnfException
{
    public function __construct(
        string $message,
        public readonly ?SourcePosition $position = null,
    ) {
        parent::__construct($position === null ? $message : sprintf('%s at %s.', $message, $position));
    }
}
