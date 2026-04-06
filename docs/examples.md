# Examples

This document explains the example scripts shipped with the library and when to use each one.

## Overview

The examples are intentionally small and focus on one workflow at a time:

- parsing a grammar definition
- loading a grammar from a file
- exporting a parsed AST
- validating real input against a grammar
- analyzing a grammar for semantic issues

The example grammars in this directory are designed to be readable first. They avoid domain-specific names from older internal grammars and instead use clear concepts such as search filters and calculator expressions.

## Example grammars

### `examples/grammars/task-search.ebnf`

A small search language for task filtering.

It supports inputs such as:

```text
status:open priority:high "payment bug"
assignee:alice tag:backend limit:10
```

This grammar is the main runtime validation example because it maps well to textarea-style query input.

### `examples/grammars/calculator.ebnf`

A readable arithmetic expression grammar.

It supports concepts such as:

- integers
- grouped expressions
- multiplication and division
- addition and subtraction

This grammar is useful for showing nesting, repetition, and non-terminal references in a compact way.

## Example scripts

### `examples/basic.php`

Minimal grammar parsing example.

Use it when you want to see the basic `Lexer -> Parser -> AST` flow with an inline grammar string.

Run:

```bash
php examples/basic.php
```

### `examples/parse-file.php`

Loads a `.ebnf` file from disk and parses it into a grammar AST.

Use it when you already have a grammar file and want to inspect the available rule names.

Run:

```bash
php examples/parse-file.php
php examples/parse-file.php examples/grammars/calculator.ebnf
```

### `examples/pretty-print.php`

Parses a grammar and renders it in two exported forms:

- normalized EBNF using `PrettyPrinter`
- JSON using `JsonAstExporter`

Use it when you want to debug, inspect, or serialize the grammar model.

Run:

```bash
php examples/pretty-print.php
```

### `examples/runtime-validate.php`

Validates a real input string against a chosen start rule using the runtime layer.

Use it when you want grammar-driven syntax validation similar to a textarea or editor integration.

Run:

```bash
php examples/runtime-validate.php
php examples/runtime-validate.php 'status:open priority:high "payment bug"'
php examples/runtime-validate.php $'status:open\npriority:'
```

### `examples/analyze-grammar.php`

Runs semantic checks against a parsed grammar.

Current checks include:

- duplicate rules
- undefined rule references
- unreachable rules

Use it when you want to validate the grammar itself before using it at runtime.

Run:

```bash
php examples/analyze-grammar.php
php examples/analyze-grammar.php examples/grammars/calculator.ebnf
```

### `examples/showcase.php`

Shows the full happy path in one place:

- parse a grammar
- pretty print it
- analyze it
- validate one correct input
- validate one incorrect input

Run:

```bash
php examples/showcase.php
```
