<?php

declare(strict_types=1);

namespace Platine\Etl\Loader;

$mock_json_encode = false;

function json_encode($str, $flags = 0, $depth = 512)
{
    global $mock_json_encode;
    if ($mock_json_encode) {
        return '';
    } else {
        return \json_encode($str, $flags, $depth);
    }
}
