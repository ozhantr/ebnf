<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Diagnostic;

final readonly class Diagnostic
{
    public function __construct(
        public DiagnosticSeverity $severity,
        public string $message,
        public ?string $rule = null,
    ) {
    }
}
