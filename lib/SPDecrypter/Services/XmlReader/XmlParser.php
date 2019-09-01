<?php
/**
 * syspass-decrypter
 *
 * @author    nuxsmin
 * @link      https://syspass.org
 * @copyright 2019-2019, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of syspass-decrypter.
 *
 * syspass-decrypter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * syspass-decrypter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with syspass-decrypter.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SPDecrypter\Services\XmlReader;

use Defuse\Crypto\Exception\CryptoException;
use DOMDocument;
use DOMElement;
use DOMXPath;
use SPDecrypter\Services\ServiceBase;
use SPDecrypter\Storage\FileException;
use SPDecrypter\Storage\FileHandler;
use SPDecrypter\Util\Crypt;
use SPDecrypter\Util\Version;

/**
 * Class XmlParser
 * @package SPDecrypter\Services\XmlReader
 */
final class XmlParser extends ServiceBase
{
    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var DOMXPath
     */
    private $xpath;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param string      $file
     * @param XmlReader   $reader
     * @param string|null $password
     *
     * @param string|null $signature
     *
     * @throws FileException
     * @throws XmlCheckerError
     * @throws XmlParserError
     * @throws XmlReaderError
     */
    public function initialize(string $file,
                               XmlReader $reader,
                               string $password = null,
                               string $signature = null)
    {
        if ($this->initialized) {
            throw new XmlParserError('XML parser already initialized');
        }

        $this->logger->info(__FUNCTION__);

        $this->document = $reader->read(new FileHandler($file));

        XmlChecker::validateSchema($this->document);

        $this->xpath = new DOMXPath($this->document);

        if ($signature) {
            $this->logger->info('Checking XML signature');

            XmlChecker::checkSignature($this->document, $signature);
        }

        if ($this->detectEncrypted()) {
            $this->logger->info('Encrypted XML detected');

            if (!$password) {
                throw new XmlParserError('Encryption password not set');
            }

            $this->processEncrypted($password);

            // Validate the schema again after decryption
            XmlChecker::validateSchema($this->document);
        }

        $this->initialized = true;
    }

    /**
     * Check whether there are encrypted data
     *
     * @return bool
     */
    private function detectEncrypted()
    {
        return ($this->document->getElementsByTagName('Encrypted')->length === 1);
    }

    /**
     * Process the encrypted data and then build the unencrypted DOM
     *
     * @param string $password
     *
     * @throws XmlParserError
     * @throws XmlCheckerError
     */
    private function processEncrypted(string $password)
    {
        XmlChecker::checkEncryptionHash($this->document, $password);

        $this->logger->info('Processing encrypted data');

        $dataNodes = $this->xpath->query('/Root/Encrypted/Data');

        $version = $this->xpath->query('/Root/Meta/Version')->item(0)->nodeValue;

        $decode = Version::checkVersion($version, '320.0');

        foreach ($dataNodes as $node) {
            /** @var $node DOMElement */
            $data = $decode ? base64_decode($node->nodeValue) : $node->nodeValue;

            try {
                $xmlDecrypted = Crypt::decrypt($data, $node->getAttribute('key'), $password);
            } catch (CryptoException $e) {
                $this->logger->error($e->getMessage());

                throw new XmlParserError('Error decrypting XML data');
            }

            $newXmlData = new DOMDocument();

            if ($newXmlData->loadXML($xmlDecrypted) === false) {
                throw new XmlParserError('Error loading XML data');
            }

            $this->document->documentElement->appendChild($this->document->importNode($newXmlData->documentElement, true));
        }

        // Remove the encrypted data after processing
        $this->document->documentElement->removeChild($dataNodes->item(0)->parentNode);
    }

    /**
     * Return the XML version
     *
     * @throws XmlParserError
     */
    public function getXmlVersion(): string
    {
        return $this->getXpath()
            ->query('/Root/Meta/Version')
            ->item(0)
            ->nodeValue;
    }

    /**
     * @return DOMXPath
     * @throws XmlParserError
     */
    public function getXpath(): DOMXPath
    {
        if (!$this->initialized) {
            throw new XmlParserError('XML parser not initialized');
        }

        return $this->xpath;
    }

    /**
     * Return the XML date
     *
     * @throws XmlParserError
     */
    public function getXmlDate(): int
    {
        return (int)$this->getXpath()
            ->query('/Root/Meta/Time')
            ->item(0)
            ->nodeValue;
    }

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}