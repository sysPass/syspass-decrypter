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

namespace Tests;

use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Faker\Factory;
use Faker\Generator;
use SPDecrypter\Storage\FileException;
use SPDecrypter\Storage\FileHandler;
use SPDecrypter\Util\Crypt;
use SPDecrypter\Util\Hash;
use XMLWriter;

final class XmlBuilder
{
    const MASTER_PASSWORD = '12345678900';
    const XML_VERSION = '320.0';
    const XML_PASSWORD = 'syspass';
    const SEED_COUNT = 1000;

    /**
     * @var XMLWriter
     */
    private $xml;
    /**
     * @var  Generator
     */
    private $faker;
    /**
     * @var FileHandler
     */
    private $file;
    /**
     * @var bool
     */
    private $encrypt;

    /**
     * XmlBuilder constructor.
     *
     * @param string $file
     * @param bool   $encrypt
     */
    public function __construct(string $file, bool $encrypt = false)
    {
        $this->faker = Factory::create();
        $this->file = new FileHandler($file);
        $this->encrypt = $encrypt;
    }
    
    /**
     * @throws CryptoException
     * @throws Exception
     */
    public function run()
    {
        $this->createDocument();
        $this->createCategories();
        $this->createClients();
        $this->createTags();
        $this->createAccounts();
        $this->writeXML();
    }

    /**
     * @throws FileException
     */
    private function createDocument()
    {
        $this->xml = new XMLWriter();
        $this->xml->openMemory();
        $this->xml->setIndent(true);
        $this->xml->startDocument('1.0', 'UTF-8');
        $this->xml->startElement('Root');

        $this->xml->startElement('Meta');

        $time = time();

        $this->xml->writeElement('Generator', 'sysPass');
        $this->xml->writeElement('Version', self::XML_VERSION);
        $this->xml->writeElement('Time', $time);

        $this->xml->startElement('User');
        $this->xml->writeAttribute('id', 1);
        $this->xml->text('TestUser');
        $this->xml->endElement();

        $this->xml->startElement('Group');
        $this->xml->writeAttribute('id', 1);
        $this->xml->text('TestGroup');
        $this->xml->endElement();

        $hash = sha1($time . self::XML_VERSION);

        $this->xml->startElement('Hash');
        $this->xml->writeAttribute('sign', Hash::signMessage($hash, self::XML_PASSWORD));
        $this->xml->text($hash);
        $this->xml->endElement();

        // Meta
        $this->xml->endElement();

        if ($this->encrypt) {
            $this->xml->writeRaw(sprintf('<Encrypted hash="%s">', Hash::hashKey(self::XML_PASSWORD)));
        }

        $this->flush();
    }

    /**
     * @throws FileException
     */
    private function flush()
    {
        $this->file->write($this->xml->outputMemory());
    }

    /**
     * @throws CryptoException
     * @throws EnvironmentIsBrokenException
     * @throws FileException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    private function createCategories()
    {
        $this->xml->startElement('Categories');

        for ($i = 1; $i <= self::SEED_COUNT; $i++) {
            $this->xml->startElement('Category');
            $this->xml->writeAttribute('id', $i);
            $this->xml->writeElement('name', $this->faker->city);
            $this->xml->writeElement('description', $this->faker->sentence);
            $this->xml->endElement();

            if (0 === $i % 500 && !$this->encrypt) {
                $this->flush();
            }
        }

        // <Categories>
        $this->xml->endElement();

        if ($this->encrypt) {
            $this->encryptNode();
        } else {
            $this->flush();
        }
    }

    /**
     * @return void
     * @throws CryptoException
     * @throws EnvironmentIsBrokenException
     * @throws FileException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    private function encryptNode()
    {
        $securedKey = Crypt::makeSecuredKey(self::XML_PASSWORD, false);
        $encrypted = Crypt::encrypt($this->xml->outputMemory(), $securedKey->unlockKey(self::XML_PASSWORD));

        $this->xml->startElement('Data');
        $this->xml->writeAttribute('key', $securedKey->saveToAsciiSafeString());
        $this->xml->text($encrypted);
        $this->xml->endElement();

        $this->flush();
    }

    /**
     * @throws CryptoException
     * @throws EnvironmentIsBrokenException
     * @throws FileException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    private function createClients()
    {
        $this->xml->startElement('Clients');

        for ($i = 1; $i <= self::SEED_COUNT; $i++) {
            $this->xml->startElement('Client');
            $this->xml->writeAttribute('id', $i);
            $this->xml->writeElement('name', $this->faker->company);
            $this->xml->writeElement('description', $this->faker->sentence);
            $this->xml->endElement();

            if (0 === $i % 500 && !$this->encrypt) {
                $this->flush();
            }
        }

        // <Clients>
        $this->xml->endElement();

        if ($this->encrypt) {
            $this->encryptNode();
        } else {
            $this->flush();
        }
    }

    /**
     * @throws CryptoException
     * @throws EnvironmentIsBrokenException
     * @throws FileException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    private function createTags()
    {
        $this->xml->startElement('Tags');

        for ($i = 1; $i <= self::SEED_COUNT; $i++) {
            $this->xml->startElement('Tag');
            $this->xml->writeAttribute('id', $i);
            $this->xml->writeElement('name', $this->faker->colorName);
            $this->xml->endElement();

            if (0 === $i % 500 && !$this->encrypt) {
                $this->flush();
            }
        }

        // <Tags>
        $this->xml->endElement();

        if ($this->encrypt) {
            $this->encryptNode();
        } else {
            $this->flush();
        }
    }

    /**
     * @throws CryptoException
     * @throws EnvironmentIsBrokenException
     * @throws FileException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    private function createAccounts()
    {
        $secureKey = Crypt::makeSecuredKey(self::MASTER_PASSWORD, false);
        $key = $secureKey->unlockKey(self::MASTER_PASSWORD);

        $this->xml->startElement('Accounts');

        for ($i = 1; $i <= self::SEED_COUNT * 10; $i++) {
            $this->xml->startElement('Account');
            $this->xml->writeAttribute('id', $i);
            $this->xml->writeElement('name', $this->faker->name);
            $this->xml->writeElement('clientId', mt_rand(1, self::SEED_COUNT));
            $this->xml->writeElement('categoryId', mt_rand(1, self::SEED_COUNT));
            $this->xml->writeElement('login', $this->faker->userName);
            $this->xml->writeElement('url', $this->faker->url);
            $this->xml->writeElement('notes', $this->faker->text);

            $this->xml->writeElement('pass',
                Crypt::encrypt($this->faker->password, $key));
            $this->xml->writeElement('key', $secureKey->saveToAsciiSafeString());

            $this->xml->startElement('tags');

            for ($j = 1; $j <= mt_rand(1, self::SEED_COUNT / 10); $j++) {
                $this->xml->startElement('tag');
                $this->xml->writeAttribute('id', mt_rand(1, self::SEED_COUNT));
                $this->xml->endElement();
            }

            // <tags>
            $this->xml->endElement();

            // <Account>
            $this->xml->endElement();

            if (0 === $i % 500 && !$this->encrypt) {
                $this->flush();
            }
        }

        // <Accounts>
        $this->xml->endElement();

        if ($this->encrypt) {
            $this->encryptNode();
        } else {
            $this->flush();
        }
    }

    /**
     * @throws Exception
     */
    private function writeXML()
    {
        if ($this->encrypt) {
            $this->xml->writeRaw('</Encrypted>');
        }

        // <Root>
        $this->xml->endElement();

        $this->flush();

        $this->file->close();
    }
}