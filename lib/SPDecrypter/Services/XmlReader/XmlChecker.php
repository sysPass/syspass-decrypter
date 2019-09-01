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

use DOMDocument;
use DOMElement;
use DOMXPath;
use SPDecrypter\Util\Hash;
use SPDecrypter\Util\Version;

/**
 * Class XmlChecker
 *
 * @package SPDecrypter\Services\XmlReader
 */
final class XmlChecker
{
    /**
     * @param DOMDocument $document
     *
     * @throws XmlCheckerError
     */
    public static function validateSchema(DOMDocument $document)
    {
        if (!$document->schemaValidate(XML_SCHEMA)) {
            throw new XmlCheckerError('Invalid XML schema');
        }
    }

    /**
     * Obtener la versión del XML
     *
     * @param DOMDocument $document
     * @param string      $key The key used for signing the XML file
     *
     * @return bool
     * @throws XmlCheckerError
     */
    public static function checkSignature(DOMDocument $document, string $key)
    {
        $hash = (new DOMXPath($document))->query('/Root/Meta/Hash');

        if ($hash->length === 1
            && $hash->item(0)->nodeType === XML_ELEMENT_NODE
            && ($sign = $hash->item(0)->attributes->getNamedItem('sign'))
        ) {
            return Hash::checkMessage($hash->item(0)->nodeValue, $key, $sign->nodeValue);
        }

        throw new XmlCheckerError('XML signature not found');
    }

    /**
     * @param DOMDocument $document
     * @param string      $password
     *
     * @throws XmlCheckerError
     */
    public static function checkEncryptionHash(DOMDocument $document, string $password)
    {
        /** @var DOMElement $encryptedNode */
        $encryptedNode = $document->getElementsByTagName('Encrypted')->item(0);
        $hash = $encryptedNode->getAttribute('hash');

        if (!empty($hash) && !Hash::checkHashKey($password, $hash)) {
            throw new XmlCheckerError('Wrong encryption password');
        }
    }

    /**
     * @param string $version
     *
     * @return void
     * @throws XmlCheckerError
     */
    public static function checkVersion(string $version)
    {
        if (Version::checkVersion($version, XML_MIN_VERSION)) {
            throw new XmlCheckerError(sprintf('Sorry, this XML version is not compatible. Please use >= %s',
                    Version::normalizeVersionForCompare(XML_MIN_VERSION))
            );
        }
    }
}