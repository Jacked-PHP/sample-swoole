<?php

namespace MyCode\Helpers;

class ArrayHelpers
{
    public static function only(array $array, array $keys)
    {
        $newArray = [];
        foreach ($array as $key => $item) {
            if (!in_array($key, $keys)) {
                continue;
            }
            $newArray[$key] = $item;
        }

        return $newArray;
    }
}