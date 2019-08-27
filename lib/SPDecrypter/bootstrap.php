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

define('APP_VERSION', [0, 1, 0, 19082401]);
define('XML_MIN_VERSION', [3, 0, 0, 0]);
define('DS', DIRECTORY_SEPARATOR);

require APP_BASE_DIR . DS . 'vendor' . DS . 'autoload.php';

use DI\ContainerBuilder;
use SPDecrypter\Commands\SearchAccountCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

try {
    $builder = new ContainerBuilder();
    $builder->addDefinitions(__DIR__ . DS . 'definitions.php');
    $dic = $builder->build();

    $app = new Application();
    $app->add(new SearchAccountCommand($dic));
    $app->run($dic->get(InputInterface::class), $dic->get(OutputInterface::class));
} catch (Exception $e) {
    echo $e->getMessage();
}