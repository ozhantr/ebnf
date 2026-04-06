<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Exporter\JsonAstExporter;
use Ozhantr\Ebnf\Exporter\PrettyPrinter;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;

require_once __DIR__ . '/../vendor/autoload.php';

$grammar = file_get_contents(__DIR__ . '/grammars/calculator.ebnf');

if ($grammar === false) {
    fwrite(STDERR, "Could not read calculator example grammar.\n");
    exit(1);
}

$ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

echo "Pretty printed grammar:\n";
echo (new PrettyPrinter())->print($ast) . "\n\n";

echo "JSON AST:\n";
echo (new JsonAstExporter())->export($ast) . "\n";
