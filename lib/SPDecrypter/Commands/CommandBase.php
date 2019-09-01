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

use League\CLImate\CLImate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CommandBase
 * @package SPDecrypter\Commands
 */
abstract class CommandBase extends Command
{
    /**
     * @var CLImate
     */
    protected $climate;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * Service constructor.
     *
     * @param CLImate         $climate
     * @param LoggerInterface $logger
     * @param SymfonyStyle    $io
     */
    public function __construct(CLImate $climate, LoggerInterface $logger, SymfonyStyle $io)
    {
        parent::__construct();

        $this->climate = $climate;
        $this->logger = $logger;
        $this->io = $io;

        $this->addOption('xmlpath',
            null,
            InputArgument::OPTIONAL,
            'sysPass XML file path',
            APP_BASE_DIR . DIRECTORY_SEPARATOR . 'syspass.xml')
            ->addOption('password',
                null,
                InputArgument::OPTIONAL,
                'XML password')
            ->addOption('masterPassword',
                null,
                InputArgument::OPTIONAL,
                'sysPass master password')
            ->addOption('wide',
                null,
                InputArgument::OPTIONAL,
                'Use full length for every field displayed',
                false)
            ->addOption('signature',
                null,
                InputArgument::OPTIONAL,
                'Signature used to sing the XML file');
    }
}