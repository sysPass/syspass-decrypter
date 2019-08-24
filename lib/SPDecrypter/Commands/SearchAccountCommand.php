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

namespace SPDecrypter\Commands;

use DateTime;
use Exception;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlSearch\SearchAdapter;
use SPDecrypter\Services\XmlSearch\XmlSearch;
use SPDecrypter\Util\Strings;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SearchAccountCommand
 * @package SPDecrypter\Commands
 */
final class SearchAccountCommand extends CommandBase
{
    protected static $defaultName = 'spd:search-account';

    protected function configure()
    {
        $this->setDescription('Search for an account.')
            ->setHelp('This command searches for an account with the given name')
            ->addArgument('name',
                InputArgument::REQUIRED,
                'Account name')
            ->addOption('withCategories',
                null,
                InputArgument::OPTIONAL,
                'Display category on search results',
                false)
            ->addOption('withTags',
                null,
                InputArgument::OPTIONAL,
                'Display tag on search results',
                false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);

        try {
            $password = $input->getOption('password');

            if (!$password) {
                $ask = $this->climate->input('XML password: ');
                $password = $ask->prompt();
            }

            $masterPassword = $input->getOption('masterPassword');

            if (!$masterPassword) {
                $ask = $this->climate->input('Master password: ');
                $masterPassword = $ask->prompt();
            }

            $parser = $this->dic->get(XmlParser::class);
            $parser->initialize($input->getOption('xmlpath'), $this->dic->get(XmlReader::class), $password);

            $adapter = $this->dic->get(SearchAdapter::class);
            $adapter->setWithCategories(Strings::boolval($input->getOption('withCategories')));
            $adapter->setWithTags(Strings::boolval($input->getOption('withTags')));
            $adapter->setTruncate(!Strings::boolval($input->getOption('wide')));

            $search = $this->dic->get(XmlSearch::class);
            $accounts = $adapter->getNodes($search->searchByName($input->getArgument('name')),
                $masterPassword);

            $xmlDate = DateTime::createFromFormat('U', $parser->getXmlDate());

            $output->writeln([
                sprintf('<info>XML file sysPass version: %s</info>', $parser->getXmlVersion()),
                sprintf('<info>XML file date: %s</info>', $xmlDate->format('c')),
                '',
                sprintf('<info>Using "%s" file for reading accounts data</info>', $input->getOption('xmlpath')),
                sprintf('<info>List of Accounts for name: "%s"</info>', $input->getArgument('name')),
                sprintf('<info>Include categories: %s</info>', $adapter->isWithCategories() ? 'yes' : 'no'),
                sprintf('<info>Include tags: %s</info>', $adapter->isWithTags() ? 'yes' : 'no'),
                sprintf('<info>Wide output: %s</info>', $adapter->isTruncate() ? 'no' : 'yes'),
                sprintf('Total items: %d', count($accounts)),
                '',
            ]);

            $this->climate->table($accounts);
        } catch (Exception $e) {
            $logger->error($e->getTraceAsString());
            $logger->error($e->getMessage());
        }
    }
}