<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Tests\Runtime;

use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use Ozhantr\Ebnf\Runtime\GrammarRuntime;
use PHPUnit\Framework\TestCase;

final class GrammarRuntimeTest extends TestCase
{
    public function testItMatchesValidInputAgainstFixtureGrammar(): void
    {
        $grammar = file_get_contents(dirname(__DIR__) . '/Fixtures/task-search.ebnf');
        self::assertNotFalse($grammar);

        $runtime = new GrammarRuntime((new Parser((new Lexer($grammar))->tokenize()))->parse());
        $result = $runtime->match('search_request', 'status:open priority:high "payment bug"');

        self::assertTrue($result->success);
        self::assertSame(strlen('status:open priority:high "payment bug"'), $result->consumedLength);
        self::assertNull($result->error);
    }

    public function testItReturnsLineAndColumnForInvalidInput(): void
    {
        $grammar = file_get_contents(dirname(__DIR__) . '/Fixtures/task-search.ebnf');
        self::assertNotFalse($grammar);

        $runtime = new GrammarRuntime((new Parser((new Lexer($grammar))->tokenize()))->parse());
        $result = $runtime->match('search_request', "status:open\npriority:");

        self::assertFalse($result->success);
        self::assertNotNull($result->error);
        self::assertSame(2, $result->error->line);
        self::assertSame(10, $result->error->column);
    }

    public function testItReportsExpectedTokenWhenTrailingInputRemains(): void
    {
        $grammar = 'number = /^[0-9]+/ ;';
        $runtime = new GrammarRuntime((new Parser((new Lexer($grammar))->tokenize()))->parse());

        $result = $runtime->match('number', '123abc');

        self::assertFalse($result->success);
        self::assertNotNull($result->error);
        self::assertSame(['EOF'], $result->error->expected);
        self::assertSame('a', $result->error->found);
    }
}
