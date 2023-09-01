<?php

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\Socket\InternetAddress;
use danog\Stun\Attribute;
use danog\Stun\StunClient;
use Webmozart\Assert\Assert;

/**
 * Represents a FINGERPRINT attribute.
 */
final class Fingerprint extends Attribute {
    private const XOR = "\x53\x54\x55\x4e";
    public const TYPE = 0x8028;
    public function __construct(
        public readonly string $crc
    )
    {
    }
    protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): Attribute
    {
        Assert::eq($length, 4, "Wrong length!");
        return new self($reader->readLength(4, $cancellation) ^ self::XOR);
    }
    protected function writeAttr(string $transactionId): string
    {
        return $this->crc ^ self::XOR;
    }
}