<?php

class Service_SecurityToken
{
    private static $saltData = [
        'MP-879___()+_==":P:>74635^%^%dd',
    ];

    /**
     * Zwraca unikalny (pod względem self::$saltData, $value i $localSalt).
     *
     * @param string $value
     * @param string $localSalt
     *
     * @return string
     */
    public static function get($value, $localSalt = null)
    {
        $saltString = implode(',', self::$saltData);
        $map = [
            $value,
            $localSalt,
            $value,
            $saltString,
            $saltString,
            $value,
            $value . $saltString,
            strrev($saltString . $localSalt . strrev($saltString)),
        ];

        return md5(implode('-', $map));
    }
}
