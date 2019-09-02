<?php

namespace Tests;

use League\CLImate\Logger;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Services\XmlReader\XmlCheckerError;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlParserError;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlReader\XmlReaderError;
use SPDecrypter\Services\XmlSearch\SearchAdapter;
use SPDecrypter\Services\XmlSearch\XmlSearch;
use SPDecrypter\Services\XmlSearch\XmlSearchError;
use SPDecrypter\Storage\FileException;

class XmlSearchTest extends TestCase
{
    const STANDARD_KEYS = ['name', 'login', 'url', 'notes', 'client', 'password'];
    const EXTENDED_KEYS = ['tags', 'category'];

    /**
     * @var XmlSearch
     */
    protected $xmlSearch;
    /**
     * @var XmlParser
     */
    protected $xmlParser;

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchByName()
    {
        $result = $this->xmlSearch->searchByName('Test');

        $this->assertEquals(0, $result->length);

        $result = $this->xmlSearch->searchByName('Citlalli');

        $this->assertEquals(1, $result->length);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchByNameAdapter()
    {
        $adapter = new SearchAdapter($this->xmlParser);

        $result = $this->xmlSearch->searchByName('Citlalli', $adapter);

        $this->assertCount(1, $result);

        $this->checkAdapter(self::STANDARD_KEYS, $result[0]);

        foreach (self::EXTENDED_KEYS as $key) {
            $this->assertArrayNotHasKey($key, $result[0]);
        }

        $this->assertEquals('**encrypted**', $result[0]['password']);
    }

    /**
     * @param array $keys
     * @param array $data
     */
    private function checkAdapter(array $keys, array $data)
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $data);
        }
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchByNameAdapterAllFields()
    {
        $adapter = new SearchAdapter($this->xmlParser);
        $adapter->setWithTags(true);
        $adapter->setWithCategories(true);

        $result = $this->xmlSearch->searchByName('Citlalli', $adapter);

        $this->assertCount(1, $result);

        $this->checkAdapter(array_merge(self::STANDARD_KEYS, self::EXTENDED_KEYS), $result[0]);

        $this->assertEquals('**encrypted**', $result[0]['password']);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchByNameAdapterWithPassword()
    {
        $adapter = new SearchAdapter($this->xmlParser);

        $this->xmlSearch->setPassword(XmlBuilder::MASTER_PASSWORD);

        $result = $this->xmlSearch->searchByName('Citlalli', $adapter);

        $this->assertCount(1, $result);

        $this->checkAdapter(self::STANDARD_KEYS, $result[0]);

        $this->assertStringNotContainsString('**encrypted**', $result[0]['password']);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchAll()
    {
        $result = $this->xmlSearch->searchAll();

        $this->assertEquals(1000, $result->length);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchAllAdapter()
    {
        $adapter = new SearchAdapter($this->xmlParser);

        $result = $this->xmlSearch->searchAll($adapter);

        $this->assertCount(1000, $result);

        $this->checkAdapter(self::STANDARD_KEYS, $result[0]);

        foreach (self::EXTENDED_KEYS as $key) {
            $this->assertArrayNotHasKey($key, $result[0]);
        }

        $this->assertEquals('**encrypted**', $result[0]['password']);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlSearchError
     */
    public function testSearchAllAdapterAllFields()
    {
        $adapter = new SearchAdapter($this->xmlParser);
        $adapter->setWithCategories(true);
        $adapter->setWithTags(true);

        $result = $this->xmlSearch->searchAll($adapter);

        $this->assertCount(1000, $result);

        $this->checkAdapter(array_merge(self::STANDARD_KEYS, self::EXTENDED_KEYS), $result[0]);

        $this->assertEquals('**encrypted**', $result[0]['password']);
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

        $this->xmlParser = new XmlParser($logger);
        $this->xmlParser->initialize(XML_UNENCRYPTED, new XmlReader($logger));
        $this->xmlSearch = new XmlSearch($this->xmlParser);
    }
}
