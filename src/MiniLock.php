<?php

declare(strict_types=1);

namespace Zegnat\MiniLock;

use SplFileObject;

/**
 * @author    Martijn van der Ven <martijn@vanderven.se>
 * @copyright 2018 Martijn van der Ven
 * @license   0BSD BSD Zero Clause License
 */
final class MiniLock implements MiniLockInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $email, string $passphrase)
    {
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
