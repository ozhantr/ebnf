<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Analyzer;

use Ozhantr\Ebnf\Diagnostic\Diagnostic;

final readonly class AnalysisResult
{
    /**
     * @param list<Diagnostic> $diagnostics
     */
    public function __construct(
        public array $diagnostics,
    ) {
    }

    public function hasErrors(): bool
    {
        foreach ($this->diagnostics as $diagnostic) {
            if ($diagnostic->severity->value === 'error') {
                return true;
            }
        }

        return false;
    }
}
