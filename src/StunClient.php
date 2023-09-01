<?php declare(strict_types=1);

namespace danog\Stun;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\ReadableBuffer;
use Amp\Socket\Socket;

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
        $this->socket = connect($endpoint);
    }

    /**
     * @return list<Attribute>
     */
    public function bind(Attribute ...$attributes): Message
    {
        $msg = new Message(MessageMethod::BINDING, MessageClass::REQUEST, $attributes, \random_bytes(12));
        $msg->write($this->socket);
        $read = new ReadableBuffer($this->socket->read());
        return Message::read(new BufferedReader($read));
    }
}
