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

use League\CLImate\CLImate;
use SPDecrypter\Services\Categories\CategoriesBuilder;
use SPDecrypter\Services\Client\ClientBuilder;
use SPDecrypter\Services\Tags\TagsBuilder;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlSearch\SearchAdapter;
use SPDecrypter\Services\XmlSearch\XmlSearch;
use function DI\autowire;
use function DI\create;

return [
    CLImate::class => create(),
    XmlReader::class => autowire(),
    XmlParser::class => autowire(),
    XmlSearch::class => autowire(),
    ClientBuilder::class => autowire(),
    CategoriesBuilder::class => autowire(),
    TagsBuilder::class => autowire(),
    SearchAdapter::class => autowire()
];