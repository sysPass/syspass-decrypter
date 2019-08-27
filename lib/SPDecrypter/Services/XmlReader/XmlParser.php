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
     * @throws XmlParserError
     */
    public function getDocument()
    {
        if (!$this->initialized) {
            throw new XmlParserError('XML parser not initialized');
        }

        return $this->document;
    }

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
    public function initialize(string $file, XmlReader $reader,
                               string $password = null,
                               string $signature = null)
    {
        if ($this->initialized) {
            throw new XmlParserError('XML parser already initialized');
        }

        $this->logger->info(__FUNCTION__);

        $this->document = $reader->read(new FileHandler($file));

        XmlChecker::checkBaseNodes($this->document);

        if ($signature) {
            $this->logger->info('Checking XML signature');

            XmlChecker::checkSignature($this->document, $signature);
        }

        if ($this->detectEncrypted()) {
            $this->logger->info('Encrypted XML detected');

            if (!$password) {
                throw new XmlParserError('Encryption password not set');
            }

            XmlChecker::checkEncryptedNodes($this->document);

            $this->processEncrypted($password);
        }

        XmlChecker::checkUnencryptedNodes($this->document);

        $this->xpath = new DOMXPath($this->document);

        $this->initialized = true;
    }

    /**
     * Check whether there are encrypted data
     *
     * @return bool
     */
    private function detectEncrypted()
    {
        return ($this->document->getElementsByTagName('Encrypted')->length > 0);
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

        $dataNodes = $this->document->getElementsByTagName('Data');

        foreach ($dataNodes as $node) {
            /** @var $node DOMElement */
            $data = base64_decode($node->nodeValue);

            try {
                $xmlDecrypted = Crypt::decrypt($data, $node->getAttribute('key'), $password);
            } catch (CryptoException $e) {
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
        $version = $this->getXpath()->query('/Root/Meta/Version');

        if (empty($version)) {
            throw new XmlParserError('Version node not found');
        }

        return $version->item(0)->nodeValue;
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
        $date = $this->getXpath()->query('/Root/Meta/Time');

        if (empty($date)) {
            throw new XmlParserError('Date node not found');
        }

        return (int)$date->item(0)->nodeValue;
    }
}