<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Analyzer\GrammarAnalyzer;
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
$result = (new GrammarAnalyzer())->analyze($ast);

printf("Analyzed: %s\n", $file);

if ($result->diagnostics === []) {
    echo "No diagnostics.\n";
    exit(0);
}

foreach ($result->diagnostics as $diagnostic) {
    printf(
        "[%s] %s%s\n",
        strtoupper($diagnostic->severity->value),
        $diagnostic->message,
        $diagnostic->rule === null ? '' : sprintf(' (rule: %s)', $diagnostic->rule)
    );
}
