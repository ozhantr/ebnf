<?php

declare(strict_types=1);

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use Ozhantr\Ebnf\Runtime\GrammarRuntime;

require_once __DIR__ . '/../vendor/autoload.php';

$grammarFile = __DIR__ . '/grammars/task-search.ebnf';
$grammar = file_get_contents($grammarFile);

if ($grammar === false) {
    fwrite(STDERR, "Could not read fixture grammar.\n");
    exit(1);
}

$input = $argv[1] ?? 'status:open priority:high "payment bug"';
$startRule = $argv[2] ?? 'search_request';

$runtime = new GrammarRuntime((new Parser((new Lexer($grammar))->tokenize()))->parse());
$result = $runtime->match($startRule, $input);

printf("Start rule: %s\n", $startRule);
printf("Input: %s\n", $input);

if ($result->success) {
    printf("Valid syntax. Consumed: %d\n", $result->consumedLength);
    exit(0);
}

printf("%s\n", $result->error?->message() ?? 'Unknown syntax error.');
exit(1);
