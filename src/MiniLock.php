<?php

declare(strict_types=1);

namespace Zegnat\MiniLock;

use SplFileObject;
use Tuupola\Base58;

/**
 * @author    Martijn van der Ven <martijn@vanderven.se>
 * @copyright 2018 Martijn van der Ven
 * @license   0BSD BSD Zero Clause License
 */
final class MiniLock implements MiniLockInterface
{
    /** @var string The user’s generated private key. */
    private $private = null;

    /** @var string The user’s generated public key. */
    private $public = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $email, string $passphrase)
    {
        $this->private = \sodium_hex2bin(\scrypt(\blake2s($passphrase, 32, '', true), $email, 2 ** 17, 8, 1, 32));
        $this->public = \sodium_crypto_scalarmult_base($this->private);
    }

    /**
     * {@inheritdoc}
     */
    public function getMiniLockID(): string
    {
        $base58 = new Base58(['characters' => Base58::BITCOIN]);
        $check = \blake2s($this->public, 1, '', true);

        return $base58->encode($this->public.$check);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(SplFileObject $from, SplFileObject $to): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(SplFileObject $from, SplFileObject $to, array $recipients): int
    {
        return 0;
    }
}
