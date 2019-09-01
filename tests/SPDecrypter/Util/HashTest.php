<?php

namespace Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Util\Hash;

/**
 * Class HashTest
 *
 * @package Tests
 */
class HashTest extends TestCase
{
    /**
     */
    public function testHashKey()
    {
        $faker = Factory::create();

        for ($i = 2; $i <= 128; $i *= 2) {
            $key = $faker->password($i);
            $hash = Hash::hashKey($key);

            $this->assertNotEmpty($hash);
            $this->assertTrue(Hash::checkHashKey($key, $hash));
        }
    }

    /**
     */
    public function testSignMessage()
    {
        $faker = Factory::create();

        for ($i = 2; $i <= 128; $i *= 2) {
            $text = $faker->text;

            $key = $faker->password($i);
            $hash = Hash::signMessage($text, $key);

            $this->assertNotEmpty($hash);
            $this->assertTrue(Hash::checkMessage($text, $key, $hash));
        }
    }
}
