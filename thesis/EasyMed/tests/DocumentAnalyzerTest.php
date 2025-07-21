<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../ai_document_analyzer.php';

class DocumentAnalyzerTest extends TestCase
{
    /**
     * @var DocumentAnalyzer
     */
    private $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new DocumentAnalyzer();
    }

    public function testAnalyzeDocumentReturnsArray()
    {
        $result = $this->analyzer->analyzeDocument('dummy.pdf', 'dummy.pdf');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('suggested_title', $result);
        $this->assertArrayHasKey('document_type', $result);
    }

    public function testCleanTextForJSONRemovesControlCharacters()
    {
        $dirtyText = "Hello\x00World\x1F!";
        $cleaned = $this->analyzer->cleanTextForJSON($dirtyText);
        $this->assertStringNotContainsString("\x00", $cleaned);
        $this->assertStringNotContainsString("\x1F", $cleaned);
    }
} 
