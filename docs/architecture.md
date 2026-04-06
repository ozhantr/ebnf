# Architecture

The library is organized as layered components so each concern can evolve independently and be reused in different workflows.

## End-to-end flow

The project now supports two connected pipelines:

1. Grammar parsing
   `EBNF source -> Lexer -> Tokens -> Parser -> Grammar AST`
2. Runtime validation
   `Grammar AST -> GrammarRuntime -> input validation result`

This separation is intentional. The first pipeline understands the grammar definition itself, while the second uses that parsed grammar to validate real user input such as textarea content, filters, or mini-DSL expressions.

## Core layers

1. `Ozhantr\Ebnf\Lexer\Lexer`
   Converts raw EBNF text into typed tokens with line and column metadata.
2. `Ozhantr\Ebnf\Token\*`
   Defines token types and immutable token value objects.
3. `Ozhantr\Ebnf\Parser\Parser`
   Uses recursive descent parsing to transform a token stream into a grammar AST.
4. `Ozhantr\Ebnf\Ast\*`
   Holds immutable nodes for grammar rules and expressions.
5. `Ozhantr\Ebnf\Exception\*`
   Provides lexical and syntax exceptions with source positions.

## Runtime layer

`Ozhantr\Ebnf\Runtime\GrammarRuntime` interprets AST nodes against a real input string.

Responsibilities:

- Match a chosen start rule against input
- Validate full-input consumption
- Return structured success or failure results
- Report `offset`, `line`, and `column` for syntax errors
- Surface expected patterns or literals when matching fails

Related classes:

- `Ozhantr\Ebnf\Runtime\GrammarRuntime`
- `Ozhantr\Ebnf\Runtime\MatchResult`
- `Ozhantr\Ebnf\Runtime\MatchError`

## Analyzer layer

`Ozhantr\Ebnf\Analyzer\GrammarAnalyzer` performs semantic checks on the parsed grammar AST before runtime execution.

Current checks:

- Duplicate rule detection
- Undefined non-terminal detection
- Unreachable rule detection

Related classes:

- `Ozhantr\Ebnf\Analyzer\GrammarAnalyzer`
- `Ozhantr\Ebnf\Analyzer\AnalysisResult`
- `Ozhantr\Ebnf\Diagnostic\Diagnostic`
- `Ozhantr\Ebnf\Diagnostic\DiagnosticSeverity`

## Export layer

These components help inspect or externalize the grammar model:

- `Ozhantr\Ebnf\Exporter\JsonAstExporter` for serialization and tooling integration
- `Ozhantr\Ebnf\Exporter\PrettyPrinter` for normalized EBNF output

## Supported grammar constructs

The parser currently supports:

- Rule definition: `name = expression ;`
- Choice: `a | b`
- Sequence: `a , b`
- Optional: `[ a ]`
- Repetition: `{ a }`
- Grouping: `( a )`
- String terminals: `"text"`
- Regex terminals: `/^[a-z]+/`
- Non-terminals: `identifier`

## AST model

Important node types include:

- `GrammarNode`
- `RuleNode`
- `ChoiceNode`
- `SequenceNode`
- `OptionalNode`
- `RepeatNode`
- `TerminalNode`
- `RegexTerminalNode`
- `NonTerminalNode`

All AST nodes are immutable to keep parsing and analysis predictable.

## Parsing strategy

Operator precedence is implemented as:

1. Primary expressions
2. Sequence
3. Choice

The parser uses recursive descent, which keeps the implementation readable and makes future language extensions straightforward.

## Current limitations

The library does not yet support the full possible EBNF ecosystem. Notable gaps today:

- Native EBNF block comments such as `(* ... *)`
- Parse tree or match tree generation for runtime validation
- Advanced semantic checks such as recursion analysis or ambiguity detection
- A dedicated high-level facade API for file-based loading and validation

## Recommended extension path

The current architecture is ready for the next additions:

1. Match tree generation for runtime parsing
2. Richer diagnostics with source excerpts and caret indicators
3. Comment support in the grammar lexer
4. Additional analyzer passes such as recursion and duplicate-entry detection
5. A façade such as `GrammarFacade::fromFile(...)->validate(...)`
