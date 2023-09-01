# Stun - A pure PHP async STUN implementation

Created by Daniil Gentili ([@danog](https://github.com/danog)).  

This is a pure PHP async STUN implementation.

Usage:

```bash
composer require danog/stun
```

And then:

```php
<?php

use danog\Stun\StunClient;

require 'vendor/autoload.php';

$stun = new StunClient("stun.l.google.com:19302");
var_dump($stun->bind());
```
