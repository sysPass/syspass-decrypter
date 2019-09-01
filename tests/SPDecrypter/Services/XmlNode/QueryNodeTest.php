<?php

namespace Tests;

use DOMXPath;
use League\CLImate\Logger;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Services\XmlNode\QueryNode;
use SPDecrypter\Services\XmlNode\QueryNodeError;
use SPDecrypter\Services\XmlReader\XmlCheckerError;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlParserError;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlReader\XmlReaderError;
use SPDecrypter\Storage\FileException;

class QueryNodeTest extends TestCase
{
    /**
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * @throws QueryNodeError
     */
    public function testGetNodes()
    {
        $this->assertEquals(1, QueryNode::getNodes(QueryNode::QUERY_CATEGORIES, $this->xpath)->length);
        $this->assertEquals(1, QueryNode::getNodes(QueryNode::QUERY_TAGS, $this->xpath)->length);
        $this->assertEquals(1, QueryNode::getNodes(QueryNode::QUERY_CLIENTS, $this->xpath)->length);
        $this->assertEquals(0, QueryNode::getNodes('/Root/Test', $this->xpath)->length);
    }

    /**
     * @throws QueryNodeError
     */
    public function testGetNodesMissing()
    {
        $this->expectException(QueryNodeError::class);
        $this->expectExceptionMessage('Error getting node \'/Root/Test@\'');

        QueryNode::getNodes('/Root/Test@', $this->xpath);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     * @throws FileException
     */
    protected function setUp(): void
    {
        $logger = new Logger();

        $xmlParser = new XmlParser($logger);
        $xmlParser->initialize(XML_UNENCRYPTED, new XmlReader($logger));
        $this->xpath = $xmlParser->getXpath();

    }
}
