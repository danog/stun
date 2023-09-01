<?php declare(strict_types=1);

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\Cancellation;
use danog\Stun\Attribute;
use Webmozart\Assert\Assert;

/**
 * Represents an ERROR-CODE attribute.
 */
final class ErrorCode extends Attribute
{
    public const TYPE = 0x0009;

    /**
     * @param int<300, 699> $err
     */
    public function __construct(
        public readonly int $err,
        public readonly string $reason
    ) {
    }
    protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): Attribute
    {
        $reader->readLength(2, $cancellation);
        $class = \ord($reader->readLength(1, $cancellation));
        $number = \ord($reader->readLength(1, $cancellation));
        Assert::true($class >= 3 && $class <= 6);
        Assert::true($number < 100);
        return new self($class*100+$number, $reader->readLength($length-4));
    }
    protected function writeAttr(string $transactionId): string
    {
        $number = $this->err % 100;
        $class = $this->err - $number;
        return "\0\0".\chr($class).\chr($number).$this->reason;
    }
}
