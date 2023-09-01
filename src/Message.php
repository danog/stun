<?php declare(strict_types=1);

namespace danog\Stun;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Webmozart\Assert\Assert;

final class Message
{
    public const MAGIC_COOKIE = "\x21\x12\xA4\x42";

    public function __construct(
        public readonly MessageMethod $method,
        public readonly MessageClass $class,
        /** @var list<Attribute> */
        public readonly array $attributes,
        public readonly string $transactionId
    ) {
        Assert::true(\strlen($transactionId) === 12);
    }
    public function write(WritableStream $writer): void
    {
        $attributes = '';
        foreach ($this->attributes as $attr) {
            $attributes .= $attr->write($this->transactionId);
        }
        $writer->write(
            \pack('nn', $this->method->value | $this->class->value, \strlen($attributes)).
            self::MAGIC_COOKIE.$this->transactionId.$attributes
        );
    }
    public static function read(BufferedReader $reader, ?Cancellation $cancellation = null): self
    {
        $type = \unpack('n', $reader->readLength(2, $cancellation))[1];
        $length = \unpack('n', $reader->readLength(2, $cancellation))[1];
        Assert::eq($length % 4, 0);
        Assert::eq($reader->readLength(4, $cancellation), self::MAGIC_COOKIE, "Wrong magic cookie!");
        $transactionId = $reader->readLength(12, $cancellation);

        $attributes = [];
        while ($length) {
            $attr = Attribute::read($reader, $length, $transactionId, $cancellation);
            if ($attr) {
                $attributes []= $attr;
            }
        }

        return new self(
            MessageMethod::from($type & MessageMethod::MASK),
            MessageClass::from($type & MessageClass::MASK),
            $attributes,
            $transactionId
        );
    }
}
