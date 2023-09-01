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
 * Represents a SOFTWARE attribute.
 */
final class Software extends Attribute {
    public const TYPE = 0x8022;
    public function __construct(
        public readonly string $software
    )
    {
    }
    protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): Attribute
    {
        return new self($reader->readLength($length, $cancellation));
    }
    protected function writeAttr(string $transactionId): string
    {
        return $this->software;
    }
}