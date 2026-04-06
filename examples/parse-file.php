<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;

require_once __DIR__ . '/../vendor/autoload.php';

$file = $argv[1] ?? __DIR__ . '/grammars/task-search.ebnf';
$grammar = file_get_contents($file);

if ($grammar === false) {
    fwrite(STDERR, sprintf("Could not read grammar file: %s\n", $file));
    exit(1);
}

$ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

printf("Parsed file: %s\n", $file);
printf("Rules: %d\n", count($ast->rules));

foreach ($ast->rules as $rule) {
    printf("- %s\n", $rule->name);
}
