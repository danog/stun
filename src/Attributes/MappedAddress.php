<?php

namespace danog\Stun\Attributes;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\Socket\InternetAddress;
use danog\Stun\Attribute;
use Webmozart\Assert\Assert;

/**
 * Represents a MAPPED-ADDRESS attribute.
 */
final class MappedAddress extends Attribute {
    public const TYPE = 0x1;
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
        $ip = $reader->readLength($len = match (ord($reader->readLength(1, $cancellation))) {
            1 => 4, 
            2 => 16
        }, $cancellation);
        Assert::eq($len+4, $length, "Wrong length!");
        $port = unpack('n', $reader->readLength(2, $cancellation))[1];
        return new self(new InternetAddress(
            inet_ntop($ip),
            $port
        ));
    }

    protected function writeAttr(string $_): string  {
        $addr = $this->address->getAddressBytes();
        return "\0".(strlen($addr) === 4 ? 1 : 16).$addr.pack('n', $this->address->getPort());
    }
}