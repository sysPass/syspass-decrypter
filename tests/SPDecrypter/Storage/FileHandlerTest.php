<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      https://syspass.org
 * @copyright 2012-2018, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of sysPass.
 *
 * sysPass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sysPass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use SPDecrypter\Storage\FileException;
use SPDecrypter\Storage\FileHandler;

/**
 * Class FileHandlerTest
 *
 * @package Tests
 */
class FileHandlerTest extends TestCase
{
    const VALID_FILE = RESOURCE_DIR . DIRECTORY_SEPARATOR . 'valid_file.test';
    const MISSING_FILE = RESOURCE_DIR . DIRECTORY_SEPARATOR . 'missing_file.test';

    /**
     * @throws FileException
     */
    public function testWrite()
    {
        $handler = new FileHandler(self::VALID_FILE);

        echo 'File: ', $handler->getFile();

        $handler->write('valid_file');

        $this->assertEquals('valid_file', $handler->readToString());

        $handler->close();

        $this->assertFileExists(self::VALID_FILE);
    }

    /**
     * @throws FileException
     */
    public function testCheckIsWritable()
    {
        $handler = (new FileHandler(self::VALID_FILE))->clearCache();

        $this->assertInstanceOf(FileHandler::class, $handler->checkIsWritable());
    }

    /**
     * @throws FileException
     */
    public function testGetFileSize()
    {
        $size = (new FileHandler(self::VALID_FILE))->getFileSize();

        $this->assertEquals(10, $size);
    }

    /**
     * @throws FileException
     */
    public function testGetFileSizeMissing()
    {
        $this->expectException(FileException::class);

        (new FileHandler(self::MISSING_FILE))->getFileSize();
    }

    /**
     * @throws FileException
     */
    public function testCheckFileExists()
    {
        $handler = (new FileHandler(self::VALID_FILE))->clearCache();

        $this->assertInstanceOf(FileHandler::class, $handler->checkFileExists());
    }

    /**
     * @throws FileException
     */
    public function testCheckFileMissing()
    {
        $this->expectException(FileException::class);

        (new FileHandler(self::MISSING_FILE))
            ->clearCache()
            ->checkFileExists();
    }

    /**
     * @throws FileException
     */
    public function testOpenAndRead()
    {
        $handler = new FileHandler(self::VALID_FILE);
        $handler->open('rb');
        $this->assertEquals('valid_file', $handler->read());
        $this->assertEquals('valid_file', $handler->readToString());
    }

    /**
     * @throws FileException
     */
    public function testOpenAndReadMissing()
    {
        $this->expectException(FileException::class);

        (new FileHandler(self::MISSING_FILE))
            ->open('rb');
    }

    /**
     * @throws FileException
     */
    public function testClose()
    {
        $handler = new FileHandler(self::VALID_FILE);
        $handler->open('rb');

        $this->assertInstanceOf(FileHandler::class, $handler->close());
    }

    /**
     * @throws FileException
     */
    public function testCloseNotOpened()
    {
        $this->expectException(FileException::class);

        (new FileHandler(self::VALID_FILE))->close();
    }

    /**
     * @throws FileException
     */
    public function testCloseMissing()
    {
        $this->expectException(FileException::class);

        (new FileHandler(self::MISSING_FILE))->close();
    }

    /**
     * @throws FileException
     */
    public function testCheckIsReadable()
    {
        $handler = (new FileHandler(self::VALID_FILE))->clearCache();

        $this->assertInstanceOf(FileHandler::class, $handler->checkIsReadable());
    }

    /**
     * @throws FileException
     */
    public function testCheckIsReadableMissing()
    {
        $this->expectException(FileException::class);

        $handler = (new FileHandler(self::MISSING_FILE))->clearCache();

        $this->assertInstanceOf(FileHandler::class, $handler->checkIsReadable());
    }

    /**
     * @throws FileException
     */
    public function testDelete()
    {
        (new FileHandler(self::VALID_FILE))->delete();

        $this->assertFileNotExists(self::VALID_FILE);
    }
}
