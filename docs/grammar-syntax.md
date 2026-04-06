# Grammar Syntax

This document explains the EBNF constructs currently supported by the library.

## Rule definitions

Each rule is defined with the form:

```ebnf
rule_name = expression ;
```

Example:

```ebnf
digit = /^[0-9]/ ;
```

This defines a rule named `digit`.

## Sequence

Use `,` when one expression must be followed by another.

```ebnf
number = digit , digit ;
```

This means: match one `digit`, then another `digit`.

## Choice

Use `|` when there are alternatives.

```ebnf
boolean = "true" | "false" ;
```

This means: match either `"true"` or `"false"`.

## Optional expressions

Use `[ ... ]` when something may appear zero or one time.

```ebnf
greeting = "hello" , [ name ] ;
```

This means: match `"hello"` and optionally match `name`.

## Repetition

Use `{ ... }` when something may repeat zero or more times.

```ebnf
digits = digit , { digit } ;
```

This means: match one `digit`, followed by zero or more additional `digit` matches.

## Grouping

Use `( ... )` to group expressions.

```ebnf
value = ("yes" | "no") , "!" ;
```

This means: first choose between `"yes"` and `"no"`, then match `"!"`.

## String terminals

Use quoted strings to match exact literal text.

```ebnf
status = "open" | "closed" ;
```

## Regex terminals

Use `/.../` to match regular expressions.

```ebnf
identifier = /^[a-zA-Z_][a-zA-Z0-9_]*/ ;
```

This is useful for names, numbers, whitespace, or token-like patterns.

## Non-terminals

A plain identifier refers to another rule.

```ebnf
number = digit , { digit } ;
```

Here `digit` is a non-terminal reference.

## Example

```ebnf
word = /^[a-zA-Z][a-zA-Z0-9_-]*/ ;

quoted_text = /^"([^"\\\\]|\\\\.)*"/ ;

tag_filter = "tag:" , word ;

text_filter = quoted_text ;

filter = tag_filter | text_filter ;

search_request = filter , { /^\s+/, filter } ;
```

This grammar defines a simple search language with tags and quoted text.

## Current limitations

Not every possible EBNF variant is supported yet. In particular:

- native EBNF comments such as `(* ... *)` are not supported yet
- match tree generation is not implemented yet
- advanced ambiguity and recursion analysis is not implemented yet
