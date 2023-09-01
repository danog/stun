<?php declare(strict_types=1);

namespace danog\Stun;

use Amp\ByteStream\BufferedReader;
use Amp\Cancellation;
use danog\Stun\Attributes\ErrorCode;
use danog\Stun\Attributes\Fingerprint;
use danog\Stun\Attributes\MappedAddress;
use danog\Stun\Attributes\MessageIntegrity;
use danog\Stun\Attributes\Software;
use danog\Stun\Attributes\Username;
use danog\Stun\Attributes\XorMappedAddress;
use Webmozart\Assert\Assert;

abstract class Attribute
{
    /**
     * @internal
     */
    public static function posmod(int $a, int $b): int
    {
        $resto = $a % $b;
        return $resto < 0 ? $resto + \abs($b) : $resto;
    }

    public function write(string $transactionId): string
    {
        $data = $this->writeAttr($transactionId);
        return \pack('n', $this::TYPE).\strlen($data).$data.\str_repeat("\0", 4 - (\strlen($data) % 4));
    }
    public static function read(BufferedReader $reader, int &$totalLength, string $transactionId, ?Cancellation $cancellation = null): ?self
    {
        $totalLength -= 4;
        Assert::true($totalLength >= 0);

        $r = \unpack('n*', $reader->readLength(4, $cancellation));
        $type = $r[1];
        $length = $r[2];
        $result = match ($type) {
            ErrorCode::TYPE => ErrorCode::class,
            Fingerprint::TYPE => Fingerprint::class,
            MessageIntegrity::TYPE => MessageIntegrity::class,
            MappedAddress::TYPE => MappedAddress::class,
            Software::TYPE => Software::class,
            Username::TYPE => Username::class,
            XorMappedAddress::TYPE => XorMappedAddress::class,
            default => null,
        };

        $left = self::posmod($length, 4);
        $totalLength -= $length + $left;
        Assert::true($totalLength >= 0);

        if ($result) {
            $result = $result::readAttr($reader, $transactionId, $length, $cancellation);
        } else {
            $reader->readLength($length, $cancellation);
        }
        if ($left) {
            $reader->readLength($left, $cancellation);
        }
        return $result;
    }

    abstract protected static function readAttr(BufferedReader $reader, string $transactionId, int $length, ?Cancellation $cancellation = null): self;
    abstract protected function writeAttr(string $transactionId): string;
}
