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

use Defuse\Crypto\Exception\CryptoException;
use DOMElement;
use DOMNode;
use DOMNodeList;
use SPDecrypter\Services\XmlNode\NodeAdapter;
use SPDecrypter\Services\XmlNode\QueryNode;
use SPDecrypter\Services\XmlNode\QueryNodeError;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlParserError;
use SPDecrypter\Util\Crypt;
use SPDecrypter\Util\Strings;

/**
 * Class SearchAdapter
 * @package SPDecrypter\Services\XmlSearch
 */
final class SearchAdapter implements SearchAdapterInterface
{
    /**
     * @var XmlParser
     */
    private $xmlParser;
    /**
     * @var bool
     */
    private $withCategories = false;
    /**
     * @var bool
     */
    private $withTags = false;
    /**
     * @var bool
     */
    private $truncate = true;

    /**
     * SearchAdapter constructor.
     *
     * @param XmlParser $xmlParser
     */
    public function __construct(XmlParser $xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    /**
     * @param DOMNodeList $nodeList
     *
     * @param string|null $password
     *
     * @return array
     * @throws CryptoException
     * @throws QueryNodeError
     * @throws XmlParserError
     */
    public function getNodes(DOMNodeList $nodeList, string $password = null): array
    {
        $xpath = $this->xmlParser->getXpath();

        $clients = NodeAdapter::arrayAdapter(
            QueryNode::getNodes(QueryNode::QUERY_CLIENTS, $xpath)
        );

        if ($this->withCategories) {
            $categories = NodeAdapter::arrayAdapter(
                QueryNode::getNodes(QueryNode::QUERY_CATEGORIES, $xpath)
            );
        }

        if ($this->withTags) {
            $tags = NodeAdapter::arrayAdapter(
                QueryNode::getNodes(QueryNode::QUERY_TAGS, $xpath)
            );
        }

        $nodes = [];

        if ($nodeList->length > 0) {
            /** @var DOMElement $node */
            foreach ($nodeList as $node) {
                $account = [];

                /** @var DOMElement $accountNode */
                foreach ($node->childNodes as $accountNode) {
                    switch ($accountNode->tagName) {
                        case 'name':
                            $account['name'] = $this->getValueString($accountNode->nodeValue);
                            break;
                        case 'login':
                            $account['login'] = $this->getValueString($accountNode->nodeValue);
                            break;
                        case 'url':
                            $account['url'] = $this->getValueString($accountNode->nodeValue);
                            break;
                        case 'notes':
                            $account['notes'] = $this->getValueString($accountNode->nodeValue);
                            break;
                        case 'clientId':
                            $account['client'] = $this->getValueString($clients[(int)$accountNode->nodeValue]);
                            break;
                        case 'categoryId':
                            if ($this->withCategories) {
                                $account['category'] = $this->getValueString($categories[(int)$accountNode->nodeValue]);
                            }
                            break;
                        case 'tags':
                            if ($this->withTags && $accountNode->childNodes->length > 0) {
                                $accountTags = [];

                                /** @var DOMNode $tagNode */
                                foreach ($accountNode->childNodes as $tagNode) {
                                    if ($tagNode->nodeType === XML_ELEMENT_NODE) {
                                        $accountTags[] = $tags[(int)$tagNode->getAttribute('id')];
                                    }
                                }

                                $account['tags'] = $this->getValueString(implode(',', $accountTags));
                            }
                            break;
                        case 'pass';
                            if ($password) {
                                $account['password'] = Crypt::decrypt($accountNode->nodeValue,
                                    $node->getElementsByTagName('key')->item(0)->nodeValue,
                                    $password);
                            } else {
                                $account['password'] = '**encrypted**';
                            }
                            break;
                    }
                }

                $nodes[] = $account;
            }
        }

        return $nodes;
    }

    private function getValueString($value): string
    {
        return $this->truncate ? Strings::truncate((string)$value, 25) : (string)$value;
    }

    /**
     * @return bool
     */
    public function isWithCategories(): bool
    {
        return $this->withCategories;
    }

    /**
     * @param bool $withCategories
     */
    public function setWithCategories(bool $withCategories): void
    {
        $this->withCategories = $withCategories;
    }

    /**
     * @return bool
     */
    public function isWithTags(): bool
    {
        return $this->withTags;
    }

    /**
     * @param bool $withTags
     */
    public function setWithTags(bool $withTags): void
    {
        $this->withTags = $withTags;
    }

    /**
     * @return bool
     */
    public function isTruncate(): bool
    {
        return $this->truncate;
    }

    /**
     * @param bool $truncate
     */
    public function setTruncate(bool $truncate): void
    {
        $this->truncate = $truncate;
    }
}