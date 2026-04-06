<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Analyzer\GrammarAnalyzer;
use Ozhantr\Ebnf\Exporter\PrettyPrinter;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use Ozhantr\Ebnf\Runtime\GrammarRuntime;

require_once __DIR__ . '/../vendor/autoload.php';

$grammarFile = __DIR__ . '/grammars/task-search.ebnf';
$grammar = file_get_contents($grammarFile);

if ($grammar === false) {
    fwrite(STDERR, "Could not read showcase grammar.\n");
    exit(1);
}

$ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

echo "Grammar summary\n";
echo "---------------\n";
printf("Rules: %d\n", count($ast->rules));
printf("Top rule: %s\n\n", $ast->rules[array_key_last($ast->rules)]->name);

echo "Pretty printed grammar\n";
echo "----------------------\n";
echo (new PrettyPrinter())->print($ast) . "\n\n";

$analysis = (new GrammarAnalyzer())->analyze($ast);
echo "Analyzer result\n";
echo "---------------\n";
echo $analysis->diagnostics === [] ? "No diagnostics.\n\n" : "Diagnostics found.\n\n";

$runtime = new GrammarRuntime($ast);

$validInput = 'status:open priority:high "payment bug"';
$validResult = $runtime->match('search_request', $validInput);

echo "Validation success example\n";
echo "--------------------------\n";
printf("Input: %s\n", $validInput);
printf("Valid: %s\n\n", $validResult->success ? 'yes' : 'no');

$invalidInput = "status:open\npriority:";
$invalidResult = $runtime->match('search_request', $invalidInput);

echo "Validation error example\n";
echo "------------------------\n";
printf("Input: %s\n", json_encode($invalidInput, JSON_THROW_ON_ERROR));
printf("Valid: %s\n", $invalidResult->success ? 'yes' : 'no');
printf("Error: %s\n", $invalidResult->error?->message() ?? 'none');
