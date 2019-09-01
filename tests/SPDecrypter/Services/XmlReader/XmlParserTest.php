<?php

namespace Tests;

use DOMXPath;
use League\CLImate\Logger;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Services\XmlReader\XmlCheckerError;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlParserError;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlReader\XmlReaderError;
use SPDecrypter\Storage\FileException;

class XmlParserTest extends TestCase
{
    /**
     * @var bool|string
     */
    protected static $tmpFile;

    /**
     * @var XmlParser
     */
    protected $xmlParser;
    /**
     * @var XmlReader
     */
    protected $xmlReader;

    public static function setUpBeforeClass(): void
    {
        self::$tmpFile = tempnam(sys_get_temp_dir(), 'syspass_');
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::$tmpFile)) {
            unlink(self::$tmpFile);
        }
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testInitializeEncrypted()
    {
        $this->xmlParser->initialize(XML_ENCRYPTED, $this->xmlReader, XmlBuilder::XML_PASSWORD);

        $this->assertTrue($this->xmlParser->isInitialized());
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testInitializeEncryptedWrongPassword()
    {
        $this->expectException(XmlCheckerError::class);
        $this->expectExceptionMessage('Wrong encryption password');

        $this->xmlParser->initialize(XML_ENCRYPTED, $this->xmlReader, 'wrong_password');
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testInitializeEncryptedNoPassword()
    {
        $this->expectException(XmlParserError::class);
        $this->expectExceptionMessage('Encryption password not set');

        $this->xmlParser->initialize(XML_ENCRYPTED, $this->xmlReader);
    }

    /**
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testInitializeUnencrypted()
    {
        $this->xmlParser->initialize(XML_UNENCRYPTED, $this->xmlReader);

        $this->assertTrue($this->xmlParser->isInitialized());
    }

    /**
     * @throws FileException
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     */
    public function testInitializeInvalidSchema()
    {
        $data = <<<'TAG'
<?xml version="1.0" encoding="UTF-8"?>
<Root>
  <Meta>
    <Generator></Generator>
    <Version>3000.0</Version>
    <Time>1567293627</Time>
    <User id="1">TestUser</User>
    <Group id="1">TestGroup</Group>
    <Hash sign="ece93527b39115f0d34e2475922b3e887a76ae17cb31361d055d64acbbebe105">cad02bccaa9008394cbd67c75f88bb1e6b16aee0</Hash>
  </Meta>
  <Categories />
  <Clients />
  <Tags />
  <Accounts />
</Root>
TAG;

        file_put_contents(self::$tmpFile, $data);

        $this->expectException(XmlCheckerError::class);

        $this->xmlParser->initialize(self::$tmpFile, $this->xmlReader);
    }

    /**
     * @throws FileException
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     */
    public function testGetXmlVersion()
    {
        $this->xmlParser->initialize(XML_UNENCRYPTED, $this->xmlReader);

        $version = $this->xmlParser->getXmlVersion();

        $this->assertIsString($version);
        $this->assertEquals(XmlBuilder::XML_VERSION, $version);
    }

    /**
     * @throws FileException
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     */
    public function testGetXmlDate()
    {
        $this->xmlParser->initialize(XML_UNENCRYPTED, $this->xmlReader);

        $date = $this->xmlParser->getXmlDate();

        $this->assertIsInt($date);
        $this->assertGreaterThan(0, $date);
    }

    /**
     * @throws FileException
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     */
    public function testGetXpath()
    {
        $this->xmlParser->initialize(XML_UNENCRYPTED, $this->xmlReader);

        $this->assertInstanceOf(DOMXPath::class, $this->xmlParser->getXpath());
    }

    /**
     * @throws XmlParserError
     */
    public function testGetXpathUninitialized()
    {
        $this->expectException(XmlParserError::class);
        $this->expectExceptionMessage('XML parser not initialized');

        $this->xmlParser->getXpath();
    }

    /**
     * @throws XmlParserError
     */
    public function testGetXmlDateUninitialized()
    {
        $this->expectException(XmlParserError::class);
        $this->expectExceptionMessage('XML parser not initialized');

        $this->xmlParser->getXmlDate();
    }

    /**
     * @throws XmlParserError
     */
    public function testGetXmlVersionUninitialized()
    {
        $this->expectException(XmlParserError::class);
        $this->expectExceptionMessage('XML parser not initialized');

        $this->xmlParser->getXmlVersion();
    }

    protected function setUp(): void
    {
        $logger = new Logger();

        $this->xmlParser = new XmlParser($logger);
        $this->xmlReader = new XmlReader($logger);
    }
}
