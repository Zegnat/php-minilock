# PHP MiniLock

A (somewhat) straight-forward implementation of reading and writing the [miniLock][] file format.

It is **NOT RECOMMENDED** to use this library when you are depending on your encryption to be faultless. Please look for audited implementations (like the official [miniLock][] application) or other formats. This library is being written as an exercise in PHP encryption only.

## Some Notes

* The entire ciphertext needs to fit within [a PHP String](https://secure.php.net/manual/en/language.types.string.php).

  This is because the used BLAKE2s implementation only runs on single strings and does not expose the usual `init`, `update`, and `final` methods required for a streaming implementation.

* When the file format writes the following:

  > The filename is then prepended to the plaintext prior to encryption. The filename is encrypted as its own 256-byte chunk (see chunking format below).

  It means the file name is never actually prepended at all. The name is encrypted separately and then taken as the first part of the ciphertext.

## Cryptographic Dependencies

The miniLock file format uses several cryptographic primitives that aren’t natively available to PHP. Not even with [Sodium](https://secure.php.net/manual/en/book.sodium.php) ([libsodium](https://libsodium.org/)) enabled. The following PHP extensions are required in addition to sodium:

1. [scrypt](https://github.com/DomBlack/php-scrypt) ([on PECL](https://pecl.php.net/package/scrypt)) for scrypt.
2. [blake2](https://github.com/strawbrary/php-blake2) for BLAKE2s.

## Other Dependencies

1. [Base58](https://github.com/tuupola/base58) is the chosen serialisation format for miniLock blobs.

## Licence

All code – except for the file [MiniLockCompatTest.php](tests/MiniLockCompatTest.php) – falls under the [0BSD][] licence. Please see [LICENSE](LICENSE) for the full text.

This library is not a “port” of the original miniLock software. Instead it is an implementation of the file format based on the file format description and tests. As such, it is not bound to the original miniLock licence ([AGPLv3][]) and is licensed differently according to its author’s preference.

[MiniLockCompatTest.php](tests/MiniLockCompatTest.php) is licensed under [AGPLv3][] because it was made specifically to match the tests found in the original miniLock repository. This is probably unneccessary, but take that legal discussion somewhere else.

[miniLock]: http://minilock.io/
[0BSD]: https://spdx.org/licenses/0BSD.html
[AGPLv3]: https://spdx.org/licenses/AGPL-3.0-only.html
