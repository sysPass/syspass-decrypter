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
use League\CLImate\CLImate;
use Psr\Log\LoggerInterface;
use SPDecrypter\Services\XmlReader\XmlParser;
use SPDecrypter\Services\XmlReader\XmlReader;
use SPDecrypter\Services\XmlSearch\SearchAdapter;
use SPDecrypter\Services\XmlSearch\XmlSearch;
use SPDecrypter\Util\Strings;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class SearchAccountCommand
 * @package SPDecrypter\Commands
 */
final class SearchAccountCommand extends CommandBase
{
    protected static $defaultName = 'spd:search-account';
    /**
     * @var XmlParser
     */
    private $xmlParser;
    /**
     * @var XmlReader
     */
    private $xmlReader;
    /**
     * @var XmlSearch
     */
    private $xmlSearch;
    /**
     * @var SearchAdapter
     */
    private $searchAdapter;

    public function __construct(CLImate $climate,
                                LoggerInterface $logger,
                                SymfonyStyle $io,
                                XmlParser $xmlParser,
                                XmlReader $xmlReader,
                                XmlSearch $xmlSearch,
                                SearchAdapter $searchAdapter)
    {
        parent::__construct($climate, $logger, $io);

        $this->xmlParser = $xmlParser;
        $this->xmlReader = $xmlReader;
        $this->xmlSearch = $xmlSearch;
        $this->searchAdapter = $searchAdapter;
    }

    protected function configure()
    {
        $this->setDescription('Search for an account.')
            ->setHelp('This command searches for an account with the given name')
            ->addArgument('name',
                InputArgument::OPTIONAL,
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
                false)
            ->addOption('export',
                null,
                InputOption::VALUE_NONE,
                'Export results to JSON and CSV files to the root files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $password = $input->getOption('password');

            if (!$password) {
                $password = $this->io->ask('XML password');
            }

            $masterPassword = $input->getOption('masterPassword');

            if (!$masterPassword) {
                $masterPassword = $this->io->ask('Master password');
            }

            $this->xmlParser->initialize(
                $input->getOption('xmlpath'),
                $this->xmlReader,
                $password,
                $input->getOption('signature')
            );

            $this->searchAdapter->setWithCategories(Strings::boolval($input->getOption('withCategories')));
            $this->searchAdapter->setWithTags(Strings::boolval($input->getOption('withTags')));
            $this->searchAdapter->setTruncate(!Strings::boolval($input->getOption('wide')));

            $this->xmlSearch->setPassword($masterPassword);

            $name = $input->getArgument('name');

            if (empty($name)) {
                $accounts = $this->xmlSearch->searchAll($this->searchAdapter);
            } else {
                $accounts = $this->xmlSearch->searchByName($input->getArgument('name'), $this->searchAdapter);
            }

            $numAccounts = count($accounts);

            $xmlDate = DateTime::createFromFormat('U', $this->xmlParser->getXmlDate());

            $output->writeln([
                '====',
                sprintf('<info>XML file sysPass version: %s</info>',
                    $this->xmlParser->getXmlVersion()),
                sprintf('<info>XML file date: %s</info>',
                    $xmlDate->format('c')),
                '====',
                sprintf('<info>Include categories: %s</info>',
                    $this->searchAdapter->isWithCategories() ? 'yes' : 'no'),
                sprintf('<info>Include tags: %s</info>',
                    $this->searchAdapter->isWithTags() ? 'yes' : 'no'),
                sprintf('<info>Wide output: %s</info>',
                    $this->searchAdapter->isTruncate() ? 'no' : 'yes'),
                '====',
            ]);

            if (empty($name)) {
                $this->io->title(sprintf('List of Accounts for name: "%s"', $input->getArgument('name')));
            } else {
                $this->io->title('List of Accounts');
            }

            if ($numAccounts > 0) {
                $this->climate->table($accounts);

                if ($input->getOption('export')) {
                    $this->exportToFiles($accounts);
                }
            }

            $this->io->success(sprintf('Total items: %d', $numAccounts));

            if (empty($masterPassword)) {
                $this->io->note('Passwords not decrypted because master password wasn\'t set');
            }

            if ($this->searchAdapter->isTruncate()) {
                $this->io->note('Truncated output for text fields');
            }
        } catch (Exception $e) {
            $this->logger->error($e->getTraceAsString());
            $this->logger->error($e->getMessage());
        }
    }

    private function exportToFiles($accounts)
    {
        // export JSON
        $fs = new Filesystem();
        $fs->dumpFile('./export.json', json_encode($accounts));

        // export CSV
        $fp = fopen('./export.csv', 'w');
        foreach ($accounts as $account) {
            fputcsv($fp, $account);
        }
    }
}