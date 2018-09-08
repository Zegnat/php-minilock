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
        /** @var string Sender’s miniLock ID. */
        $senderMiniLockID = $this->getMiniLockID();
        /** @var string 32 byte curve25519 secret key for this encryption only. */
        $senderEphemeralSecret = \sodium_crypto_box_secretkey($senderEphemeral = \sodium_crypto_box_keypair());
        /** @var string 32 byte curve25519 secret key for this encryption only. */
        $senderEphemeralPublic = \sodium_crypto_box_publickey($senderEphemeral);
        /** @var string 32 byte symmetrical key for this encryption only. */
        $fileKey = \random_bytes(32);
        /** @var string 16 byte nonce for this encryption only. */
        $fileNonce = \random_bytes(16);
        /** @var string 8 byte file identifier (magic bytes). */
        $magicBytes = 'miniLock';
        /** @var int Stream encryption chunk size. */
        $chunkSize = 1024 ** 2;
        /** @var Base58 Encoder matching the miniLock used 58 character alphabet. */
        $base58 = new Base58(['characters' => Base58::BITCOIN]);

        $ciphertext = [];
        $counter = 0;

        $fileName = $from->getFilename();
        if (256 > $length = \mb_strlen($fileName, '8bit')) {
            $fileName = \str_repeat("\x00", 256 - $length).$fileName;
        } elseif (256 < $length) {
            $fileName = \mb_substr($fileName, 0, 256, '8bit');
        }
        $ciphertext[] = "\x00\x01\x00\x00"; // Length of 256 bytes.
        $ciphertext[] = \sodium_crypto_secretbox($fileName, $fileNonce.\pack('P', $counter++), $fileKey);

        while (false === $from->eof()) {
            $chunk = $from->fread($chunkSize);
            $ciphertext[] = $this->length($chunk);
            $chunkNonce = $fileNonce.\pack('P', $counter++);
            if ($from->eof()) {
                $chunkNonce[23] = $chunkNonce[23] | "\x80";
            }
            $ciphertext[] = \sodium_crypto_secretbox($chunk, $chunkNonce, $fileKey);
        }

        $ciphertext = \implode('', $ciphertext);

        $header = [
            'version' => 1,
            'ephemeral' => \sodium_bin2base64($senderEphemeralPublic, \SODIUM_BASE64_VARIANT_ORIGINAL),
            'decryptInfo' => [],
        ];
        $fileInfo = \json_encode([
            'fileKey' => \sodium_bin2base64($fileKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            'fileNonce' => \sodium_bin2base64($fileNonce, \SODIUM_BASE64_VARIANT_ORIGINAL),
            'fileHash' => \sodium_bin2base64(\blake2s($ciphertext, 32, '', true), \SODIUM_BASE64_VARIANT_ORIGINAL),
        ]);

        foreach ($recipients as $recipient) {
            $recipientNonce = \random_bytes(24);
            $recipientPublic = \mb_substr($base58->decode($recipient), 0, 32, '8bit');
            $recipientFileInfoKey = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                $this->private, // Sender’s secret key.
                $recipientPublic
            );
            $recipientDecryptInfoKey = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                $senderEphemeralSecret,
                $recipientPublic
            );
            $recipientFileInfo = \sodium_bin2base64(
                \sodium_crypto_box(\json_encode($fileInfo), $recipientNonce, $recipientFileInfoKey),
                \SODIUM_BASE64_VARIANT_ORIGINAL
            );
            $recipientDecryptInfo = \sodium_bin2base64(
                \sodium_crypto_box(\json_encode([
                    'senderID' => $senderMiniLockID,
                    'recipientID' => $recipient,
                    'fileInfo' => $recipientFileInfo,
                ]), $recipientNonce, $recipientDecryptInfoKey),
                \SODIUM_BASE64_VARIANT_ORIGINAL
            );
            $recipientNonce = \sodium_bin2base64($recipientNonce, \SODIUM_BASE64_VARIANT_ORIGINAL);
            $header['decryptInfo'][$recipientNonce] = $recipientDecryptInfo;
        }

        $header = \json_encode($header);

        $total = 0;
        $total += $to->fwrite($magicBytes);
        $total += $to->fwrite($this->length($header));
        $total += $to->fwrite($header);
        $total += $to->fwrite($ciphertext);

        return $total;
    }

    /**
     * Binary string length as 4 byte little endian.
     *
     * @see https://stackoverflow.com/a/11544982
     * @see https://secure.php.net/manual/en/function.pack.php
     *
     * @param string $data the binary blob to count
     *
     * @return string the binary blob length
     */
    private function length(string $data): string
    {
        return \pack('V', \mb_strlen($data, '8bit'));
    }
}
