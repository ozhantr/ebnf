<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Diagnostic;

enum DiagnosticSeverity: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
}
