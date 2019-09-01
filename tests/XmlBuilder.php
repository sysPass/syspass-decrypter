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
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Faker\Factory;
use Faker\Generator;
use SPDecrypter\Util\Crypt;
use SPDecrypter\Util\Hash;

final class XmlBuilder
{
    const MASTER_PASSWORD = '12345678900';
    const XML_VERSION = '3000.0';
    const XML_PASSWORD = 'syspass';
    const SEED_COUNT = 1000;

    /**
     * @var DOMDocument
     */
    private $xml;
    /**
     * @var DOMElement
     */
    private $root;
    /**
     * @var  Generator
     */
    private $faker;
    /**
     * @var string
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
        $this->file = $file;
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

    private function createDocument()
    {
        $this->xml = new DOMDocument('1.0', 'UTF-8');
        $this->root = $this->xml->appendChild($this->xml->createElement('Root'));

        $nodeMeta = $this->xml->createElement('Meta');
        $metaGenerator = $this->xml->createElement('Generator', 'sysPass');
        $metaVersion = $this->xml->createElement('Version', self::XML_VERSION);
        $metaTime = $this->xml->createElement('Time', time());
        $metaUser = $this->xml->createElement('User', 'TestUser');
        $metaUser->setAttribute('id', 1);
        $metaGroup = $this->xml->createElement('Group', 'TestGroup');
        $metaGroup->setAttribute('id', 1);

        $nodeMeta->appendChild($metaGenerator);
        $nodeMeta->appendChild($metaVersion);
        $nodeMeta->appendChild($metaTime);
        $nodeMeta->appendChild($metaUser);
        $nodeMeta->appendChild($metaGroup);

        $this->root->appendChild($nodeMeta);
    }

    /**
     * @throws CryptoException
     */
    private function createCategories()
    {
        $node = $this->xml->createElement('Categories');

        for ($i = 1; $i <= self::SEED_COUNT; $i++) {
            $name = $this->xml->createElement('name', $this->faker->city);
            $description = $this->xml->createElement('description', $this->faker->sentence);

            $nodeChild = $this->xml->createElement('Category');
            $nodeChild->setAttribute('id', $i);
            $nodeChild->appendChild($name);
            $nodeChild->appendChild($description);

            $node->appendChild($nodeChild);
        }

        if ($this->encrypt) {
            $this->root->appendChild($this->encryptNode($node));
        } else {
            $this->root->appendChild($node);
        }
    }

    /**
     * @param DOMElement $node
     *
     * @return DOMElement
     * @throws CryptoException
     */
    private function encryptNode(DOMElement $node): DOMElement
    {
        $nodeXML = $this->xml->saveXML($node);

        $securedKey = Crypt::makeSecuredKey(self::XML_PASSWORD, false);
        $encrypted = Crypt::encrypt($nodeXML, $securedKey->unlockKey(self::XML_PASSWORD));

        $encryptedData = $this->xml->createElement('Data', base64_encode($encrypted));

        $encryptedDataKey = $this->xml->createAttribute('key');
        $encryptedDataKey->value = $securedKey->saveToAsciiSafeString();

        $encryptedData->appendChild($encryptedDataKey);

        $encryptedNode = $this->root->getElementsByTagName('Encrypted');

        if ($encryptedNode->length === 0) {
            $newNode = $this->xml->createElement('Encrypted');
            $newNode->setAttribute('hash', Hash::hashKey(self::XML_PASSWORD));
            $newNode->appendChild($encryptedData);
        } else {
            $newNode = $encryptedNode->item(0);
            $newNode->appendChild($encryptedData);
        }

        return $newNode;
    }

    /**
     * @throws CryptoException
     */
    private function createClients()
    {
        $node = $this->xml->createElement('Clients');

        for ($i = 1; $i <= self::SEED_COUNT; $i++) {
            $name = $this->xml->createElement('name', $this->faker->company);
            $description = $this->xml->createElement('description', $this->faker->sentence);

            $nodeChild = $this->xml->createElement('Client');
            $nodeChild->setAttribute('id', $i);
            $nodeChild->appendChild($name);
            $nodeChild->appendChild($description);

            $node->appendChild($nodeChild);
        }

        if ($this->encrypt) {
            $this->root->appendChild($this->encryptNode($node));
        } else {
            $this->root->appendChild($node);
        }
    }

    /**
     * @throws CryptoException
     */
    private function createTags()
    {
        $node = $this->xml->createElement('Tags');

        for ($i = 1; $i <= self::SEED_COUNT; $i++) {
            $name = $this->xml->createElement('name', $this->faker->colorName);

            $nodeChild = $this->xml->createElement('Tag');
            $nodeChild->setAttribute('id', $i);
            $nodeChild->appendChild($name);

            $node->appendChild($nodeChild);
        }

        if ($this->encrypt) {
            $this->root->appendChild($this->encryptNode($node));
        } else {
            $this->root->appendChild($node);
        }
    }

    /**
     * @throws CryptoException
     */
    private function createAccounts()
    {
        $node = $this->xml->createElement('Accounts');

        for ($i = 1; $i <= self::SEED_COUNT * 10; $i++) {
            $name = $this->xml->createElement('name', $this->faker->name);
            $clientId = $this->xml->createElement('clientId', mt_rand(1, self::SEED_COUNT));
            $categoryId = $this->xml->createElement('categoryId', mt_rand(1, self::SEED_COUNT));
            $login = $this->xml->createElement('login', $this->faker->userName);
            $url = $this->xml->createElement('url', $this->faker->url);
            $notes = $this->xml->createElement('notes', $this->faker->text);

            $secureKey = Crypt::makeSecuredKey(self::MASTER_PASSWORD, false);
            $encryptedPass = Crypt::encrypt($this->faker->password, $secureKey->unlockKey(self::MASTER_PASSWORD));

            $pass = $this->xml->createElement('pass', $encryptedPass);
            $key = $this->xml->createElement('key', $secureKey->saveToAsciiSafeString());
            $tags = $this->xml->createElement('tags');

            for ($j = 1; $j <= mt_rand(1, self::SEED_COUNT / 10); $j++) {
                $tag = $this->xml->createElement('tag');
                $tag->setAttribute('id', mt_rand(1, self::SEED_COUNT));

                $tags->appendChild($tag);
            }

            $nodeChild = $this->xml->createElement('Account');
            $nodeChild->setAttribute('id', $i);
            $nodeChild->appendChild($name);
            $nodeChild->appendChild($clientId);
            $nodeChild->appendChild($categoryId);
            $nodeChild->appendChild($login);
            $nodeChild->appendChild($url);
            $nodeChild->appendChild($notes);
            $nodeChild->appendChild($pass);
            $nodeChild->appendChild($key);
            $nodeChild->appendChild($tags);

            $node->appendChild($nodeChild);
        }

        if ($this->encrypt) {
            $this->root->appendChild($this->encryptNode($node));
        } else {
            $this->root->appendChild($node);
        }
    }

    /**
     * @throws Exception
     */
    private function writeXML()
    {
        $this->createHash();

        $this->xml->formatOutput = true;
        $this->xml->preserveWhiteSpace = false;

        if (!$this->xml->save($this->file)) {
            throw new Exception('Error while creating the XML file');
        }
    }

    private function createHash()
    {
        $data = '';

        foreach ((new DOMXPath($this->xml))->query('/Root/*[not(self::Meta)]') as $node) {
            $data .= $this->xml->saveXML($node);
        }

        $hash = sha1($data);

        $hashNode = $this->xml->createElement('Hash', $hash);
        $hashNode->appendChild($this->xml->createAttribute('sign'));

        $hashNode->setAttribute('sign', Hash::signMessage($hash, self::XML_PASSWORD));

        $this->root
            ->getElementsByTagName('Meta')
            ->item(0)
            ->appendChild($hashNode);
    }
}