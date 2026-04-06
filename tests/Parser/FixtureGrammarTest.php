<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Tests\Parser;

use Ozhantr\Ebnf\Ast\GrammarNode;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FixtureGrammarTest extends TestCase
{
    #[DataProvider('fixtureProvider')]
    public function testItParsesFixtureGrammarFiles(string $fixturePath, int $expectedRuleCount): void
    {
        $grammar = file_get_contents($fixturePath);

        self::assertNotFalse($grammar);

        $ast = (new Parser((new Lexer($grammar))->tokenize()))->parse();

        self::assertInstanceOf(GrammarNode::class, $ast);
        self::assertCount($expectedRuleCount, $ast->rules);
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function fixtureProvider(): iterable
    {
        $base = dirname(__DIR__) . '/Fixtures';

        yield 'task-search' => [$base . '/task-search.ebnf', 15];
        yield 'calculator' => [$base . '/calculator.ebnf', 7];
        yield 'config' => [$base . '/config.ebnf', 9];
    }
}
