<?php

namespace danog\Stun;

enum MessageClass : int {
    case REQUEST = 0b0000_0000_0000_0000;
    case INDICATION = 0b0000_0000_0001_0000;
    case SUCCESS_RESPONSE = 0b0000_0001_0000_0000;
    case ERROR_RESPONSE = 0b0000_0001_0001_0000;

    public const MASK = self::ERROR_RESPONSE->value;
}