<?php

use Braiba\Spymaster\SpymasterBot;

require __DIR__ . '/vendor/autoload.php';

$bot = new SpymasterBot('spymaster_bot');
$bot->tick();
