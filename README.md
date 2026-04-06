# EBNF

Framework-agnostic PHP 8.2+ library for lexing, parsing, analyzing, and validating EBNF grammars.

## Features

- Strictly typed token system
- Line/column-aware lexer
- Recursive descent parser
- Immutable AST nodes
- Rich syntax exceptions
- Regex terminals such as `/^[a-z]+/`
- Grammar analyzer for duplicate, undefined, and unreachable rules
- Runtime validation with line/column-aware syntax errors
- JSON export and pretty-print helpers

## Installation

```bash
composer install
```

For library consumers:

```bash
composer require ozhantr/ebnf
```

## Quick Start

Parse a grammar definition into an AST:

```php
<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;

$grammar = 'digit = /^[0-9]/ ; number = digit , { digit } ;';

$tokens = (new Lexer($grammar))->tokenize();
$ast = (new Parser($tokens))->parse();
```

Regex terminals are also supported:

```php
$grammar = 'identifier = /^[a-z_][a-z0-9_]*/ ;';
```

Validate real input against a start rule:

```php
<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use Ozhantr\Ebnf\Runtime\GrammarRuntime;

$grammar = 'number = /^[0-9]+/ ;';
$ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

$runtime = new GrammarRuntime($ast);
$result = $runtime->match('number', '12345');
```

Analyze a grammar for semantic issues:

```php
<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Analyzer\GrammarAnalyzer;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;

$grammar = 'start = missing ;';
$ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

$analysis = (new GrammarAnalyzer())->analyze($ast);
```

## Architecture

The library is organized into separate grammar parsing, runtime validation, analyzer, and export layers. See [docs/architecture.md](docs/architecture.md) for the current design.

## Examples Guide

For a short guide to each script in `examples/`, see [docs/examples.md](docs/examples.md).

## Running The Example

Install dependencies first so Composer can generate the autoloader:

```bash
composer install
php examples/basic.php
```

See [examples/basic.php](examples/basic.php) for the runnable example.

Additional examples:

```bash
php examples/parse-file.php
php examples/parse-file.php examples/grammars/calculator.ebnf
php examples/pretty-print.php
php examples/runtime-validate.php
php examples/runtime-validate.php 'status:open priority:high "payment bug"'
php examples/runtime-validate.php $'status:open\npriority:'
php examples/analyze-grammar.php
php examples/showcase.php
```

## Current Scope

Supported today:

- Rule definitions, choices, sequences, optionals, repetitions, and grouping
- String and regex terminals
- Grammar AST export and pretty-printing
- Runtime validation against a chosen start rule
- Semantic grammar analysis

Not yet supported:

- Native EBNF comments such as `(* ... *)`
- Match tree generation
- Advanced ambiguity or recursion analysis
