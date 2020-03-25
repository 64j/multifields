<?php

namespace Multifields\Base;

class Compress
{
    /**
     * @param $buffer
     * @return string
     */
    public static function js($buffer)
    {
        $buffer = preg_replace("/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/", "", $buffer);
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $buffer);

        return $buffer;
    }

    /**
     * @param $buffer
     * @return string
     */
    public static function css($buffer)
    {
        $buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer);
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $buffer);

        return $buffer;
    }
}
