<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tuupola\Base58;
use Zegnat\MiniLock\MiniLock;

/**
 * Test compatibility with the original miniLock implementation.
 *
 * @author    Martijn van der Ven <martijn@vanderven.se>
 * @copyright 2018 Martijn van der Ven
 * @copyright 2014 Nadim Kobeissi (original tests)
 * @license   AGPL-3.0-only GNU Affero General Public License v3.0 only
 *
 * @see       https://github.com/kaepora/miniLock/tree/master/test/tests
 */
final class MiniLockCompatTest extends TestCase
{
    /**
     * @see https://github.com/kaepora/miniLock/blob/master/test/tests/deriveKey.js
     */
    public function testDeriveKey()
    {
        $object = new MiniLock('miniLockScrypt..', 'This passphrase is supposed to be good enough for miniLock. :-)');
        $publicKey = $this->getObjectAttribute($object, 'public');
        $secretKey = $this->getObjectAttribute($object, 'private');

        $this->assertInternalType('string', $publicKey, 'Public key type check');
        $this->assertInternalType('string', $secretKey, 'Secret key type check');
        $this->assertSame(32, \mb_strlen($publicKey, '8bit'), 'Public key length');
        $this->assertSame(32, \mb_strlen($secretKey, '8bit'), 'Secret key length');
        $this->assertSame(
            'EWVHJniXUFNBC9RmXe45c8bqgiAEDoL3Qojy2hKt4c4e',
            (new Base58(['characters' => Base58::BITCOIN]))->encode($publicKey),
            'Public key Base58 representation'
        );
        $this->assertSame(
            '6rcsdGAhF2rIltBRL+gwvQTQT7JMyei/d2JDrWoo0yw=',
            \sodium_bin2base64($secretKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            'Secret key Base64 representation'
        );
        $this->assertSame(
            '22d9pyWnHVGQTzCCKYEYbL4YmtGfjMVV3e5JeJUzLNum8A',
            $object->getMiniLockID(),
            'miniLock ID from public key'
        );
    }
}
