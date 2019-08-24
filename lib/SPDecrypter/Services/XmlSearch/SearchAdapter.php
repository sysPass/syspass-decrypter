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
use SPDecrypter\Services\Categories\CategoriesBuilder;
use SPDecrypter\Services\Categories\CategoriesBuilderError;
use SPDecrypter\Services\Client\ClientBuilder;
use SPDecrypter\Services\Client\ClientBuilderError;
use SPDecrypter\Services\Tags\TagsBuilder;
use SPDecrypter\Services\Tags\TagsBuilderError;
use SPDecrypter\Util\Crypt;
use SPDecrypter\Util\Strings;

/**
 * Class SearchAdapter
 * @package SPDecrypter\Services\XmlSearch
 */
final class SearchAdapter
{
    /**
     * @var CategoriesBuilder
     */
    private $categoriesBuilder;
    /**
     * @var ClientBuilder
     */
    private $clientBuilder;
    /**
     * @var TagsBuilder
     */
    private $tagsBuilder;
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
     * @param CategoriesBuilder $categoriesBuilder
     * @param ClientBuilder     $clientBuilder
     * @param TagsBuilder       $tagsBuilder
     */
    public function __construct(CategoriesBuilder $categoriesBuilder,
                                ClientBuilder $clientBuilder,
                                TagsBuilder $tagsBuilder)
    {
        $this->categoriesBuilder = $categoriesBuilder;
        $this->clientBuilder = $clientBuilder;
        $this->tagsBuilder = $tagsBuilder;
    }

    /**
     * @param DOMNodeList $nodeList
     *
     * @param string|null $password
     *
     * @return array
     * @throws CryptoException
     * @throws CategoriesBuilderError
     * @throws ClientBuilderError
     * @throws TagsBuilderError
     */
    public function getNodes(DOMNodeList $nodeList, string $password = null): array
    {
        $clients = ClientBuilder::mapper($this->clientBuilder->getClients());

        if ($this->withCategories) {
            $categories = CategoriesBuilder::mapper($this->categoriesBuilder->getCategories());
        }

        if ($this->withTags) {
            $tags = TagsBuilder::mapper($this->tagsBuilder->getTags());
        }

        $nodes = [];

        if ($nodeList->length === 0) {
            return $nodes;
        }

        /** @var DOMNode $node */
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

                            /** @var DOMElement $tagNode */
                            foreach ($accountNode->childNodes as $tagNode) {
                                $accountTags[] = $tags[(int)$tagNode->getAttribute('id')];
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