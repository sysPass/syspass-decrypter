<?php

namespace Tests;

use DOMDocument;
use League\CLImate\Logger;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlReader\XmlReaderError;
use SPDecrypter\Storage\FileException;
use SPDecrypter\Storage\FileHandler;

class XmlReaderTest extends TestCase
{
    /**
     * @var XmlReader
     */
    protected $xmlReader;

    /**
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testReadEncrypted()
    {
        $this->assertInstanceOf(DOMDocument::class,
            $this->xmlReader->read(new FileHandler(XML_ENCRYPTED)));
    }

    /**
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testReadUnencrypted()
    {
        $this->assertInstanceOf(DOMDocument::class,
            $this->xmlReader->read(new FileHandler(XML_UNENCRYPTED)));
    }

    /**
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testReadMissing()
    {
        $this->expectException(FileException::class);

        $this->xmlReader->read(new FileHandler('some_file.xml'));
    }

    /**
     * @throws XmlReaderError
     * @throws FileException
     */
    public function testReadInvalid()
    {
        $data = <<<'TAG'
<Root>
</Root>
TAG;
        $file = tempnam(sys_get_temp_dir(), 'syspass_');

        file_put_contents($file, $data);

        $this->expectException(XmlReaderError::class);

        $this->xmlReader->read(new FileHandler($file));

        unlink($file);
    }

    protected function setUp(): void
    {
        $this->xmlReader = new XmlReader(new Logger());
    }
}
