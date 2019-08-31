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

namespace SPDecrypter\Services\XmlReader;

use DOMDocument;
use SPDecrypter\Services\ServiceBase;
use SPDecrypter\Storage\FileException;
use SPDecrypter\Storage\FileHandler;

/**
 * Class XmlReader
 * @package SPDecrypter\Services\XmlReader
 */
final class XmlReader extends ServiceBase
{
    const ALLOWED_MIME = ['application/xml', 'text/xml'];
    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * XmlReader constructor.
     *
     * @param FileHandler $fileHandler
     *
     * @return DOMDocument
     * @throws FileException
     * @throws XmlReaderError
     */
    public function read(FileHandler $fileHandler): DOMDocument
    {
        $this->logger->info(sprintf('Reading XML file "%s"', $fileHandler->getFile()));

        $this->fileHandler = $fileHandler;

        $this->checkFile();
        $this->readXMLFile();

        return $this->document;
    }

    /**
     * Check file
     *
     * @throws FileException
     * @throws XmlReaderError
     */
    private function checkFile()
    {
        $this->logger->info(__FUNCTION__);

        $this->fileHandler->checkFileExists();

        if (!in_array($this->fileHandler->getFileType(), self::ALLOWED_MIME)) {
            throw new XmlReaderError('File type not allowed');
        }
    }

    /**
     * Read the file to an XML object
     *
     * @throws FileException
     * @throws XmlReaderError
     */
    private function readXMLFile()
    {
        $this->logger->info(__FUNCTION__);

        libxml_use_internal_errors(true);

        $this->document = new DOMDocument();
        $this->document->formatOutput = false;
        $this->document->preserveWhiteSpace = false;

        if ($this->document->loadXML($this->readFileToString()) === false) {
            foreach (libxml_get_errors() as $error) {
                $this->logger->error($error->message);
            }

            throw new XmlReaderError('Unable to process the XML file');
        }
    }

    /**
     * Read the file data to a string
     *
     * @throws FileException
     */
    private function readFileToString(): string
    {
        $this->logger->info(__FUNCTION__);

        $this->autodetectEOL();

        return $this->fileHandler->readToString();
    }

    /**
     * Enable End Of Line detection
     */
    private function autodetectEOL()
    {
        $this->logger->info(__FUNCTION__);

        ini_set('auto_detect_line_endings', true);
    }
}