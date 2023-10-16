<?php

declare(strict_types=1);

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
