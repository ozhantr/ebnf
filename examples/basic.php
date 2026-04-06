<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;

require_once __DIR__ . '/../vendor/autoload.php';

$grammar = <<<EBNF
greeting = "hello" , [ /^\s+/ , name ] ;
name = /^[A-Z][a-z]+/ ;
EBNF;

$lexer = new Lexer($grammar);
$tokens = $lexer->tokenize();

$parser = new Parser($tokens);
$ast = $parser->parse();

printf("Rule count: %d\n", count($ast->rules));

foreach ($ast->rules as $rule) {
    printf("- %s\n", $rule->name);
}
