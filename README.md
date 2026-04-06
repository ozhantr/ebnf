# EBNF

Framework-agnostic PHP 8.2+ library for reading `.ebnf` grammar files, parsing EBNF syntax, analyzing grammar definitions, and validating input against those grammars.

## What Is EBNF?

`EBNF` stands for `Extended Backus-Naur Form`.

It is a formal notation used to describe the syntax of a language or mini-language. Developers often use EBNF to define things like:

- search query languages
- filter expressions
- calculator-like expressions
- config formats
- small domain-specific languages

## What This Library Does

This library helps you work with grammars defined in EBNF.

You can use it to:

- read `.ebnf` grammar files from disk
- parse those grammar definitions into a structured AST
- analyze grammars for semantic issues
- validate user input against a chosen grammar rule
- report syntax errors with line and column information

In practice, this is useful when you want to build features such as custom query inputs, textarea validation, editor tooling, or mini-DSL parsers in PHP.

## Typical Use Cases

You might use this library when you want to:

- validate a custom search or filter input
- parse a small expression language
- define and enforce a config file format
- build grammar-driven textarea or editor validation
- experiment with or debug EBNF grammars in PHP

## Features

- Strictly typed token system
- Line/column-aware lexer
- Recursive descent parser
- Immutable AST nodes
- Rich syntax exceptions
- Support for inline grammar strings and `.ebnf` grammar files
- Regex terminals such as `/^[a-z]+/`
- Grammar analyzer for duplicate, undefined, and unreachable rules
- Runtime validation with line/column-aware syntax errors
- JSON export and pretty-print helpers

## Key Terms

- `Lexer`: breaks grammar text into small meaningful pieces called `tokens`, such as names, strings, and symbols.
- `Parser`: turns those tokens into a structured grammar model.
- `AST`: an Abstract Syntax Tree, the structured representation of a parsed grammar.

## Installation

```bash
composer require ozhantr/ebnf
```

## Development Setup

If you want to work on the library itself:

```bash
git clone https://github.com/ozhantr/ebnf.git
cd ebnf
composer install
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

You can also load a `.ebnf` file from disk and parse it:

```php
<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;

$grammar = file_get_contents('path/to/grammar.ebnf');

if ($grammar === false) {
    throw new RuntimeException('Could not read grammar file.');
}

$ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();
```

Example grammar files in this repository:

- [examples/grammars/task-search.ebnf](examples/grammars/task-search.ebnf)
- [examples/grammars/calculator.ebnf](examples/grammars/calculator.ebnf)
- [tests/Fixtures/config.ebnf](tests/Fixtures/config.ebnf)

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

## End-to-End Example

Imagine that you want to validate a custom search box in your application.

1. Define the search syntax in an EBNF grammar file such as [examples/grammars/task-search.ebnf](examples/grammars/task-search.ebnf).
2. Parse that grammar into an AST.
3. Use the runtime layer to validate real user input against a start rule.

For example, this input is valid for the `search_request` rule:

```text
status:open priority:high "payment bug"
```

And this input is invalid:

```text
status:open
priority:
```

The runtime validator can report that failure with line and column information, which makes it suitable for textarea validation, editor tooling, or custom query inputs.

## Architecture

The library is organized into separate grammar parsing, runtime validation, analyzer, and export layers. See [docs/architecture.md](docs/architecture.md) for the current design.

## Examples Guide

For a short guide to each script in `examples/`, see [docs/examples.md](docs/examples.md).

## Running Examples

Install dependencies first if you are working from the repository source:

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
