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
use SPDecrypter\Util\Hash;

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
     * @throws FileException
     * @throws XmlParserError
     * @throws XmlReaderError
     */
    public function initialize(string $file, XmlReader $reader, string $password = null)
    {
        if ($this->initialized) {
            throw new XmlParserError('XML parser already initialized');
        }

        $this->document = $reader->read(new FileHandler($file));

        if ($this->detectEncrypted()) {
            if (!$password) {
                throw new XmlParserError('Encryption password not set');
            }
            $this->processEncrypted($password);
        }

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
     */
    private function processEncrypted(string $password)
    {
        $hash = $this->document->getElementsByTagName('Encrypted')->item(0)->getAttribute('hash');

        if (!empty($hash) && !Hash::checkHashKey($password, $hash)) {
            throw new XmlParserError('Wrong encryption password');
        }

        foreach ($this->document->getElementsByTagName('Data') as $node) {
            /** @var $node DOMElement */
            $data = base64_decode($node->nodeValue);

            try {
                $xmlDecrypted = Crypt::decrypt($data, $node->getAttribute('key'), $password);
            } catch (CryptoException $e) {
                continue;
            }

            $newXmlData = new DOMDocument();

            if ($newXmlData->loadXML($xmlDecrypted) === false) {
                throw new XmlParserError('Wrong encryption password');
            }

            $this->document->documentElement->appendChild($this->document->importNode($newXmlData->documentElement, TRUE));
        }

        // Remove the encrypted data before processing
        if ($this->document->getElementsByTagName('Data')->length > 0) {
            $nodeData = $this->document->getElementsByTagName('Encrypted')->item(0);
            $nodeData->parentNode->removeChild($nodeData);
        }
    }

    /**
     * Return the XML version
     * @throws XmlParserError
     */
    public function getXmlVersion()
    {
        return $this->getXpath()->query('/Root/Meta/Version')->item(0)->nodeValue;
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
     * @throws XmlParserError
     */
    public function getXmlDate()
    {
        return $this->getXpath()->query('/Root/Meta/Time')->item(0)->nodeValue;
    }
}