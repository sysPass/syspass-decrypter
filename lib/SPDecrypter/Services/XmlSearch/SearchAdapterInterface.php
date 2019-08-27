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

/**
 * Interface SearchAdapterInterface
 * @package SPDecrypter\Services\XmlSearch
 */
interface SearchAdapterInterface
{
    /**
     * @param DOMNodeList $nodeList
     * @param string|null $password
     *
     * @return array
     */
    public function getNodes(DOMNodeList $nodeList, string $password = null): array;

    /**
     * Returns if categories output is active
     *
     * @return bool
     */
    public function isWithCategories(): bool;

    /**
     * Set categories output active
     *
     * @param bool $withCategories
     */
    public function setWithCategories(bool $withCategories): void;

    /**
     * Returns if tags output is active
     *
     * @return bool
     */
    public function isWithTags(): bool;

    /**
     * Set tags output active
     *
     * @param bool $withTags
     */
    public function setWithTags(bool $withTags): void;

    /**
     * Returns if truncate fields output is active
     *
     * @return bool
     */
    public function isTruncate(): bool;

    /**
     * Truncate fields output
     *
     * @param bool $truncate
     */
    public function setTruncate(bool $truncate): void;
}