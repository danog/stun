<?php declare(strict_types=1);

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\Cancellation;
use danog\Stun\Attribute;
use Webmozart\Assert\Assert;

/**
 * Represents a MESSAGE-INTEGRITY attribute.
 */
final class MessageIntegrity extends Attribute
{
    public const TYPE = 0x8;
    public function __construct(
        public readonly string $hmac
    ) {
    }
    protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): Attribute
    {
        Assert::eq($length, 20, "Wrong length!");
        return new self($reader->readLength(20, $cancellation));
    }
    protected function writeAttr(string $transactionId): string
    {
        return $this->hmac;
    }
}
