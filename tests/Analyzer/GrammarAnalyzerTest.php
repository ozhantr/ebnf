<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Tests\Analyzer;

use Ozhantr\Ebnf\Analyzer\GrammarAnalyzer;
use Ozhantr\Ebnf\Diagnostic\DiagnosticSeverity;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class GrammarAnalyzerTest extends TestCase
{
    public function testItDetectsUndefinedRuleReferences(): void
    {
        $grammar = 'start = missing ;';
        $ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

        $result = (new GrammarAnalyzer())->analyze($ast);

        self::assertCount(1, $result->diagnostics);
        self::assertSame(DiagnosticSeverity::ERROR, $result->diagnostics[0]->severity);
        self::assertStringContainsString('undefined rule "missing"', $result->diagnostics[0]->message);
    }

    public function testItDetectsDuplicateRules(): void
    {
        $grammar = <<<EBNF
start = "a" ;
start = "b" ;
EBNF;
        $ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

        $result = (new GrammarAnalyzer())->analyze($ast);

        self::assertCount(1, $result->diagnostics);
        self::assertSame(DiagnosticSeverity::ERROR, $result->diagnostics[0]->severity);
        self::assertStringContainsString('Duplicate rule "start"', $result->diagnostics[0]->message);
    }

    public function testItDetectsUnreachableRules(): void
    {
        $grammar = <<<EBNF
value = "a" ;
unused = "b" ;
start = value ;
EBNF;
        $ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

        $result = (new GrammarAnalyzer())->analyze($ast);

        self::assertCount(1, $result->diagnostics);
        self::assertSame(DiagnosticSeverity::WARNING, $result->diagnostics[0]->severity);
        self::assertStringContainsString('Rule "unused" is unreachable', $result->diagnostics[0]->message);
    }

    public function testItReturnsNoDiagnosticsForHealthyGrammar(): void
    {
        $grammar = file_get_contents(dirname(__DIR__) . '/Fixtures/task-search.ebnf');
        self::assertNotFalse($grammar);

        $ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();
        $result = (new GrammarAnalyzer())->analyze($ast);

        self::assertCount(0, $result->diagnostics);
        self::assertFalse($result->hasErrors());
    }
}
