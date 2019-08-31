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
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SPDecrypter\Services\XmlReader\XmlChecker;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlSearch\SearchAdapter;
use SPDecrypter\Services\XmlSearch\XmlSearch;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function DI\autowire;
use function DI\create;
use function DI\factory;

return [
    CLImate::class => create(),
    XmlReader::class => autowire(),
    XmlParser::class => autowire(),
    XmlSearch::class => autowire(),
    XmlChecker::class => autowire(),
    SearchAdapter::class => autowire(),
    OutputInterface::class => create(ConsoleOutput::class)
        ->constructor(ConsoleOutput::VERBOSITY_NORMAL, true),
    InputInterface::class => create(ArgvInput::class),
    LoggerInterface::class => factory(function (ContainerInterface $c) {
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        ];

        return new ConsoleLogger($c->get(OutputInterface::class), $verbosityLevelMap);
    }),
];