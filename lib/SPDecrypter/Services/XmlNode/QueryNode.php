<?php
/**
 * syspass-decrypter
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2019 Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of syspass-decrypter.
 *
 * sysPass is free software: you can redistribute it and/or modify
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
 * along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace SPDecrypter\Services\XmlNode;

use DOMNodeList;
use DOMXPath;

/**
 * Class QueryNode
 * @package SPDecrypter\Services\XmlNode
 */
final class QueryNode
{
    const QUERY_TAGS = '/Root/Tags';
    const QUERY_CATEGORIES = '/Root/Categories';
    const QUERY_CLIENTS = '/Root/Clients';

    /**
     * @param string   $node
     * @param DOMXPath $DOMXPath
     *
     * @return DOMNodeList
     * @throws QueryNodeError
     */
    public static function getNodes(string $node, DOMXPath $DOMXPath): DOMNodeList
    {
        $check = $DOMXPath->evaluate($node);

        if ($check === false) {
            throw new QueryNodeError('Wrong node');
        }

        $nodes = $DOMXPath->query($node);

        if ($nodes === false) {
            throw new QueryNodeError(sprintf('Error getting node \'%s\'', $node));
        }

        return $nodes;
    }
}