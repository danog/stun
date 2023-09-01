<?php declare(strict_types=1);

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\Cancellation;
use danog\Stun\Attribute;

/**
 * Represents a SOFTWARE attribute.
 */
final class Software extends Attribute
{
    public const TYPE = 0x8022;
    public function __construct(
        public readonly string $software
    ) {
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
