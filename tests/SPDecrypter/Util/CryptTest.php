<?php

namespace Tests;

use Defuse\Crypto\Exception\CryptoException;
use PHPUnit\Framework\TestCase;
use SPDecrypter\Util\Crypt;

/**
 * Class CryptTest
 *
 * @package Tests
 */
class CryptTest extends TestCase
{
    const PASSWORD = 'test_password';

    /**
     * @throws CryptoException
     */
    public function testMakeSecuredKey()
    {
        $this->assertTrue(true);

        return Crypt::makeSecuredKey(self::PASSWORD);
    }

    /**
     * @depends testMakeSecuredKey
     *
     * @param string $key
     *
     * @throws CryptoException
     */
    public function testUnlockSecuredKey($key)
    {
        $this->assertTrue(true);

        Crypt::unlockSecuredKey($key, self::PASSWORD);
    }

    /**
     * @depends testMakeSecuredKey
     *
     * @param string $key
     *
     * @throws CryptoException
     */
    public function testUnlockSecuredKeyWithWrongPassword($key)
    {
        $this->expectException(CryptoException::class);

        Crypt::unlockSecuredKey($key, 'test');
    }

    /**
     * @depends testMakeSecuredKey
     *
     * @param string $key
     *
     * @throws CryptoException
     */
    public function testEncryptAndDecrypt($key)
    {
        $data = Crypt::encrypt('testdata', $key, self::PASSWORD);

        $this->assertSame('testdata', Crypt::decrypt($data, $key, self::PASSWORD));
    }

    /**
     * @depends testMakeSecuredKey
     *
     * @param string $key
     *
     * @throws CryptoException
     */
    public function testEncryptAndDecryptWithDifferentPassword($key)
    {
        $data = Crypt::encrypt('testdata', $key, self::PASSWORD);

        $this->expectException(CryptoException::class);

        Crypt::decrypt($data, $key, 'test');
    }
}
