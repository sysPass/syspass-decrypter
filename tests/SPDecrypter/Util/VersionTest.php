<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use SPDecrypter\Util\Version;

class VersionTest extends TestCase
{

    /**
     * @dataProvider checkVersionProvider
     *
     * @param $version
     * @param $expected
     */
    public function testCheckVersion($version, $expected)
    {
        $this->assertEquals($expected, Version::checkVersion($version, XML_MIN_VERSION));
    }

    /**
     * @dataProvider normalizeVersionProvider
     *
     * @param $version
     * @param $expected
     */
    public function testNormalizeVersionForCompare($version, $expected)
    {
        $this->assertEquals($expected, Version::normalizeVersionForCompare($version));
    }

    public function normalizeVersionProvider()
    {
        return [
            ['200.0', '2000.0'],
            ['3010.0', '3010.0'],
            ['301.0', '3010.0'],
            ['31010.190901', '3101.190901'],
            [[3, 0, 0, 190901], '3000.190901'],
        ];
    }

    public function checkVersionProvider()
    {
        return [
            ['200.0', true],
            ['210.0', true],
            ['300.0', false],
            ['300.190901', false],
            ['310.0', false],
            ['320.0', false],
            ['400.0', false],
        ];
    }
}
