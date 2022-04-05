<?php

namespace Helper;

use SplFileObject;

class FileHelper
{
    public static function getContents($fileName)
    {
        $fileContents = new SplFileObject($fileName);
        $fileContents->setFlags(
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE
        );

        return $fileContents;
    }
}