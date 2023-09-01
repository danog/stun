<?php

use danog\Stun\StunClient;

require 'vendor/autoload.php';

$stun = new StunClient("udp://stun.l.google.com:19302");
var_dump($stun->bind());