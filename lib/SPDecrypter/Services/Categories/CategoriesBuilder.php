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

namespace SPDecrypter\Services\Categories;

use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Psr\Container\ContainerInterface;
use SPDecrypter\Services\ServiceBase;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlParserError;

/**
 * Class CategoriesBuilder
 * @package SPDecrypter\Services\Categories
 */
final class CategoriesBuilder extends ServiceBase
{
    /**
     * @var DOMXPath
     */
    private $xpath;

    /**
     * XmlSearch constructor.
     *
     * @param ContainerInterface $dic *
     *
     * @throws XmlParserError
     */
    public function __construct(ContainerInterface $dic)
    {
        parent::__construct($dic);

        $this->xpath = new DOMXPath($dic->get(XmlParser::class)->getDocument());
    }

    public static function mapper(DOMNodeList $nodeList): array
    {
        $nodes = [];

        if ($nodeList->length === 0) {
            return $nodes;
        }

        /** @var DOMNode $node */
        foreach ($nodeList as $node) {
            /** @var DOMElement $tag */
            foreach ($node->getElementsByTagName('name') as $tag) {
                $id = (int)$tag->parentNode->getAttribute('id');

                $nodes[$id] = $tag->nodeValue;
            }
        }

        return $nodes;
    }

    /**
     * @throws CategoriesBuilderError
     */
    public function getCategories(): DOMNodeList
    {
        $nodes = $this->xpath->query('/Root/Categories');

        if ($nodes === false) {
            throw new CategoriesBuilderError('Error getting categories');
        }

        return $nodes;
    }
}