<?php

declare(strict_types=1);

namespace Platine\Etl\Fixture;

use SplFileObject;

class MySplFileObject extends SplFileObject
{
    public function fwrite($str, $length = null)
    {
        return 0;
    }
}
