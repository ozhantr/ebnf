<?php

declare(strict_types=1);

namespace Ozhantr\Ebnf\Tests\Exporter;

use Ozhantr\Ebnf\Exporter\JsonAstExporter;
use Ozhantr\Ebnf\Exporter\PrettyPrinter;
use Ozhantr\Ebnf\Lexer\Lexer;
use Ozhantr\Ebnf\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class ExporterTest extends TestCase
{
    public function testPrettyPrinterNormalizesGrammar(): void
    {
        $ast = (new Parser((new Lexer('number = digit,{"0"};'))->tokenize()))->parse();

        self::assertSame('number = digit , { "0" } ;', (new PrettyPrinter())->print($ast));
    }

    public function testJsonExporterOutputsRuleNames(): void
    {
        $ast = (new Parser((new Lexer('digit = "0" ;'))->tokenize()))->parse();
        $json = (new JsonAstExporter())->export($ast);

        self::assertStringContainsString('"name": "digit"', $json);
        self::assertStringContainsString('"type": "grammar"', $json);
    }

    public function testPrettyPrinterPrintsRegexTerminals(): void
    {
        $ast = (new Parser((new Lexer('attribut = /^[a-z]+/ ;'))->tokenize()))->parse();

        self::assertSame('attribut = /^[a-z]+/ ;', (new PrettyPrinter())->print($ast));
    }
}
