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


namespace Platine\Etl\Extractor;

$mock_is_readable_to_false = false;

function is_readable($str)
{
    global $mock_is_readable_to_false;
    if ($mock_is_readable_to_false) {
        return false;
    } else {
        return \is_readable($str);
    }
}
