<?php declare(strict_types=1);

namespace danog\Stun;

enum MessageMethod: int
{
    case BINDING = 0b0000_0000_0000_0001;

    public const MASK = ~MessageClass::MASK;
}
