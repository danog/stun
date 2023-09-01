<?php declare(strict_types=1);

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\Cancellation;
use Amp\Socket\InternetAddress;
use danog\Stun\Attribute;
use danog\Stun\Message;
use Webmozart\Assert\Assert;

/**
 * Represents a XOR-MAPPED-ADDRESS attribute.
 */
final class XorMappedAddress extends Attribute
{
    public const TYPE = 0x20;
    public function __construct(
        /**
         * IP address and port.
         */
        public readonly InternetAddress $address,
    ) {
    }

    protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): self
    {
        Assert::greaterThanEq($length, 8);
        $reader->readLength(1, $cancellation);
        $type = \ord($reader->readLength(1, $cancellation));
        $port = \unpack('n', $reader->readLength(2, $cancellation) ^ \substr(Message::MAGIC_COOKIE, 2))[1];
        $ip = match ($type) {
            1 => $reader->readLength($len = 4, $cancellation) ^ Message::MAGIC_COOKIE,
            2 => $reader->readLength($len = 16, $cancellation) ^ (Message::MAGIC_COOKIE.$transactionId)
        };
        Assert::eq($len+4, $length);
        return new self(new InternetAddress(
            \inet_ntop($ip),
            $port
        ));
    }

    protected function writeAttr(string $transactionId): string
    {
        $addr = $this->address->getAddressBytes();
        if (\strlen($addr) === 4) {
            $addr ^= Message::MAGIC_COOKIE;
        } else {
            $addr ^= Message::MAGIC_COOKIE.$transactionId;
        }
        return "\0".(\strlen($addr) === 4 ? 1 : 16).(\pack('n', $this->address->getPort()) ^ \substr(Message::MAGIC_COOKIE, 2)).$addr;
    }
}
