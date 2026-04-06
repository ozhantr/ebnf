<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Tests\Parser;

use Ozhantr\Ebnf\Ast\ChoiceNode;
use Ozhantr\Ebnf\Ast\GrammarNode;
use Ozhantr\Ebnf\Ast\RegexTerminalNode;
use Ozhantr\Ebnf\Ast\RepeatNode;
use Ozhantr\Ebnf\Ast\RuleNode;
use Ozhantr\Ebnf\Ast\SequenceNode;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testItBuildsAstForSimpleGrammar(): void
    {
        $grammar = 'number = digit , { digit } | "0" ;';

        $parser = new Parser((new Lexer($grammar))->tokenize());
        $ast = $parser->parse();

        self::assertInstanceOf(GrammarNode::class, $ast);
        self::assertCount(1, $ast->rules);
        self::assertInstanceOf(RuleNode::class, $ast->rules[0]);
        self::assertInstanceOf(ChoiceNode::class, $ast->rules[0]->expression);

        $left = $ast->rules[0]->expression->alternatives[0];
        self::assertInstanceOf(SequenceNode::class, $left);
        self::assertInstanceOf(RepeatNode::class, $left->elements[1]);
    }

    public function testItBuildsAstForRegexTerminalRules(): void
    {
        $grammar = 'attribut = /^[a-z][a-z0-9]*/ ;';

        $parser = new Parser((new Lexer($grammar))->tokenize());
        $ast = $parser->parse();

        self::assertInstanceOf(RegexTerminalNode::class, $ast->rules[0]->expression);
        self::assertSame('^[a-z][a-z0-9]*', $ast->rules[0]->expression->pattern);
    }
}
