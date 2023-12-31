<?php declare(strict_types=1);

namespace danog\Stun;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\ReadableBuffer;
use Amp\Socket\Socket;
use Webmozart\Assert\Assert;

use function Amp\Socket\connect;

final class StunClient
{
    private Socket $socket;

    /**
     * Pending outgoing requests.
     *
     * @var array<string, Message>
     */
    private array $pendingOutgoing = [];
    public function __construct(
        private string $endpoint
    ) {
        $this->socket = connect("udp://$endpoint");
    }

    public function __destruct()
    {
        $this->socket->close();
    }

    /** @no-named-arguments */
    public function bind(Attribute ...$attributes): Message
    {
        $msg = new Message(MessageMethod::BINDING, MessageClass::REQUEST, $attributes, $id = \random_bytes(12));
        $msg->write($this->socket);
        $read = new ReadableBuffer($this->socket->read());
        $msg = Message::read(new BufferedReader($read));
        Assert::eq($msg->transactionId, $id);
        return $msg;
    }
}
