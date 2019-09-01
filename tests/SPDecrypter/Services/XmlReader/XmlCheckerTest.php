<?php

namespace Tests;

use League\CLImate\Logger;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Services\XmlReader\XmlChecker;
use SPDecrypter\Services\XmlReader\XmlCheckerError;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlReader\XmlReaderError;
use SPDecrypter\Storage\FileException;
use SPDecrypter\Storage\FileHandler;

class XmlCheckerTest extends TestCase
{
    private $document;

    /**
     * @throws XmlCheckerError
     */
    public function testCheckSignature()
    {
        $this->assertTrue(XmlChecker::checkSignature($this->document, XmlBuilder::XML_PASSWORD));
    }

    /**
     * @throws XmlCheckerError
     */
    public function testCheckSignatureInvalid()
    {
        $this->assertFalse(XmlChecker::checkSignature($this->document, 'wrong_key'));
    }

    /**
     * @throws XmlCheckerError
     */
    public function testCheckEncryptionHash()
    {
        XmlChecker::checkEncryptionHash($this->document, XmlBuilder::XML_PASSWORD);

        $this->assertTrue(true);
    }

    /**
     * @throws XmlCheckerError
     */
    public function testCheckEncryptionHashWrongPassword()
    {
        $this->expectException(XmlCheckerError::class);

        XmlChecker::checkEncryptionHash($this->document, 'wrong_password');
    }

    /**
     * @param $check
     * @param $expected
     *
     * @throws XmlCheckerError
     * @dataProvider versionProvider
     */
    public function testCheckVersion($check, $expected)
    {
        if (is_bool($expected)) {
            XmlChecker::checkVersion($check);

            $this->assertTrue(true);
        } else {
            $this->expectException($expected);

            XmlChecker::checkVersion($check);
        }
    }

    /**
     * @throws XmlCheckerError
     */
    public function testValidateSchema()
    {
        XmlChecker::validateSchema($this->document);

        $this->assertTrue(true);
    }

    public function versionProvider()
    {
        return [
            ['200.0', XmlCheckerError::class],
            ['210.0', XmlCheckerError::class],
            ['300.0', true],
            ['310.0', true],
            ['320.0', true],
            ['400.0', true],
        ];
    }

    /**
     * @throws XmlReaderError
     * @throws FileException
     */
    protected function setUp(): void
    {
        $this->document = (new XmlReader(new Logger()))->read(new FileHandler(XML_ENCRYPTED));
    }
}
