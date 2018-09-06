<?php

declare(strict_types=1);

namespace Zegnat\MiniLock;

use SplFileObject;

/**
 * @author    Martijn van der Ven <martijn@vanderven.se>
 * @copyright 2018 Martijn van der Ven
 * @license   0BSD BSD Zero Clause License
 */
interface MiniLockInterface
{
    /**
     * Create a MiniLock instance using the user’s credentials.
     *
     * @param string $email      the user’s email address
     * @param string $passphrase the user’s pass phrase
     */
    public function __construct(string $email, string $passphrase);

    /**
     * Decrypt a file.
     *
     * @param SplFileObject $from the file that will be decrypted
     * @param SplFileObject $to   the file where the decrypted message will
     *                            be stored
     *
     * @return int the amount of decrypted bytes written
     */
    public function decrypt(SplFileObject $from, SplFileObject $to): int;

    /**
     * Encrypt a file.
     *
     * @param SplFileObject $from       the file that will be encrypted
     * @param SplFileObject $to         the file where the encrypted message will
     *                                  be stored
     * @param string[]      $recipients an array of miniLock IDs that will be able
     *                                  to decrypt the encrypted message
     *
     * @return int the amount of encrypted bytes written
     */
    public function encrypt(SplFileObject $from, SplFileObject $to, array $recipients): int;
}
