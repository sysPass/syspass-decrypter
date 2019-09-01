<?php

namespace Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Util\Strings;

class StringsTest extends TestCase
{
    public function testTruncate()
    {
        $faker = Factory::create();

        $text = Strings::truncate($faker->text(100), 50);

        $this->assertTrue(strlen($text) <= 53);
    }

    public function testBoolval()
    {
        $this->assertTrue(Strings::boolval('yes'));
        $this->assertTrue(Strings::boolval('y'));
        $this->assertTrue(Strings::boolval('on'));
        $this->assertTrue(Strings::boolval(1));

        $this->assertFalse(Strings::boolval('no'));
        $this->assertFalse(Strings::boolval('n'));
        $this->assertFalse(Strings::boolval('off'));
        $this->assertFalse(Strings::boolval(0));
    }
}
