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

namespace SPDecrypter\Services\XmlSearch;

use DOMNodeList;
use SPDecrypter\Services\XmlReader\XmlChecker;
use SPDecrypter\Services\XmlReader\XmlCheckerError;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlParserError;

/**
 * Class XmlSearch
 * @package SPDecrypter\Services\XmlSearch
 */
final class XmlSearch
{
    /**
     * @var XmlParser
     */
    private $xmlParser;
    /**
     * @var string
     */
    private $password;

    /**
     * XmlSearch constructor.
     *
     * @param XmlParser $xmlParser
     */
    public function __construct(XmlParser $xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    /**
     * @param                        $name
     *
     * @param SearchAdapterInterface $searchAdapter
     *
     * @return array|DOMNodeList
     * @throws XmlParserError
     * @throws XmlSearchError
     * @throws XmlCheckerError
     */
    public function searchByName($name, SearchAdapterInterface $searchAdapter = null)
    {
        XmlChecker::checkVersion($this->xmlParser->getXmlVersion());

        $nodes = $this->xmlParser
            ->getXpath()
            ->query(sprintf('/Root/Accounts/Account[contains(name, \'%s\')]', $name));

        if ($nodes === false) {
            throw new XmlSearchError('Error getting accounts');
        }

        if ($searchAdapter) {
            return $searchAdapter->getNodes($nodes, $this->password);
        }

        return $nodes;
    }

    /**
     * @param SearchAdapterInterface $searchAdapter
     *
     * @return array|DOMNodeList
     * @throws XmlParserError
     * @throws XmlSearchError
     * @throws XmlCheckerError
     */
    public function searchAll(SearchAdapterInterface $searchAdapter = null)
    {
        XmlChecker::checkVersion($this->xmlParser->getXmlVersion());

        $nodes = $this->xmlParser
            ->getXpath()
            ->query('/Root/Accounts/Account');

        if ($nodes === false) {
            throw new XmlSearchError('Error getting accounts');
        }

        if ($searchAdapter) {
            return $searchAdapter->getNodes($nodes, $this->password);
        }

        return $nodes;
    }

    /**
     * @param string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}