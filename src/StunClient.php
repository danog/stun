<?php

namespace danog\Stun;

use Amp\Socket\Socket;

use function Amp\Socket\connect;

final class StunClient {
    private Socket $socket;

    /**
     * Pending outgoing requests
     *
     * @var array<string, Message>
     */
    private array $pendingOutgoing = [];
    public function __construct(
        private string $endpoint
    )
    {
        $this->socket = connect($endpoint);
    }

    /**
     * @return list<Attribute>
     */
    public function bind(Attribute ...$attributes): array {

    }
}