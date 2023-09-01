<?php

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\Socket\InternetAddress;
use danog\Stun\Attribute;
use danog\Stun\Message;
use danog\Stun\StunClient;
use Webmozart\Assert\Assert;

/**
 * Represents a XOR-MAPPED-ADDRESS attribute.
 */
final class XorMappedAddress extends Attribute {
    public const TYPE = 0x20;
    public function __construct(
        /**
         * IP address and port
         */
        public readonly InternetAddress $address,
    )
    {
    }

    protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): self
    {
        Assert::true($length >= 8, "Wrong length!");
        $reader->readLength(1, $cancellation);
        $ip = match ($len = ord($reader->readLength(1, $cancellation))) {
            1 => $reader->readLength(4, $cancellation) ^ Message::MAGIC_COOKIE, 
            2 => $reader->readLength(16, $cancellation) ^ (Message::MAGIC_COOKIE.$transactionId)
        };
        $port = unpack('n', $reader->readLength(2, $cancellation) ^ substr(Message::MAGIC_COOKIE, 2))[1];
        Assert::eq($len+4, $length, "Wrong length!");
        return new self(new InternetAddress(
            inet_ntop($ip),
            $port
        ));
    }

    protected function writeAttr(string $transactionId): string {
        $addr = $this->address->getAddressBytes();
        if (strlen($addr) === 4) {
            $addr ^= Message::MAGIC_COOKIE;
        } else {
            $addr ^= Message::MAGIC_COOKIE.$transactionId;
        }
        return "\0".(strlen($addr) === 4 ? 1 : 16).$addr.(pack('n', $this->address->getPort()) ^ substr(Message::MAGIC_COOKIE, 2));
    }
}