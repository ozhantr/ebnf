<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Runtime;

final readonly class MatchError
{
    /**
     * @param list<string> $expected
     */
    public function __construct(
        public int $offset,
        public int $line,
        public int $column,
        public array $expected,
        public ?string $found,
        public ?string $rule = null,
    ) {
    }

    public function message(): string
    {
        $expected = implode(', ', $this->expected);
        $found = $this->found === null ? 'EOF' : $this->found;

        return sprintf(
            'Syntax error at line %d, column %d: expected %s, found %s.',
            $this->line,
            $this->column,
            $expected,
            $found
        );
    }
}
