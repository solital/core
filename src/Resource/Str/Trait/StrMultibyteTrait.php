<?php

namespace Solital\Core\Resource\Str\Trait;

trait StrMultibyteTrait
{
    protected static function mbContains(string $haystack, string $needle): bool
    {
        return $needle === '' || mb_substr_count($haystack, $needle, (empty($encoding) ? mb_internal_encoding() : $encoding)) > 0;
    }

    protected static function mbCountChars(string $string, int $mode = 0, string $encoding = 'UTF-8'): mixed
    {
        $length = mb_strlen($string, $encoding);
        $char_counts = [];

        for ($i = 0; $i < $length; ++$i) {
            $char = mb_substr($string, $i, 1, $encoding);

            if (!array_key_exists($char, $char_counts)) {
                $char_counts[$char] = 0;
            }

            ++$char_counts[$char];
        }

        return match ($mode) {
            0 => $char_counts,
            1 => array_filter($char_counts, static fn ($count): bool => $count > 0),
            2 => array_filter($char_counts, static fn ($count): bool => 0 === $count),
            3 => implode('', array_unique(mb_str_split($string, 1, $encoding))),
            4 => implode('', array_filter(array_unique(mb_str_split($string, 1, $encoding)), static fn ($char): bool => 0 === $char_counts[$char])),
            default => throw new \ValueError('Argument #2 ($mode) must be between 0 and 4 (inclusive)'),
        };
    }

    protected static function mbWordCount(string $str, int $format, ?string $charlist)
    {
        if ($format < 0 || $format > 2) {
            throw new \InvalidArgumentException('Argument #2 ($format) must be a valid format value');
        }

        if ($charlist === null) {
            $charlist = "";
        }

        $count = preg_match_all('#[\p{L}\p{N}][\p{L}\p{N}\'' . $charlist . ']*#u', $str, $matches, $format === 2 ? PREG_OFFSET_CAPTURE : PREG_PATTERN_ORDER);

        if ($format === 0) {
            return $count;
        }

        $matches = $matches[0] ?? [];

        if ($format === 2) {
            $result = [];

            foreach ($matches as $match) {
                $result[$match[1]] = $match[0];
            }

            return $result;
        }

        return $matches;
    }

    protected function mbChunkSplit(string $str, int $length = 76, string $end = "\r\n"): string
    {
        $pattern = '~.{1,' . $length . '}~u'; // like "~.{1,76}~u"
        $str = preg_replace($pattern, '$0' . $end, $str);
        return rtrim($str, $end);
    }

    protected function mbStrrev(string $string, string $encoding = 'UTF-8'): string
    {
        $length = mb_strlen($string, $encoding);
        $reversed = '';

        while ($length-- > 0) {
            $reversed .= mb_substr($string, $length, 1, $encoding);
        }

        return $reversed;
    }

    protected function mbShuffle(string $str): string
    {
        $tmp = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
        shuffle($tmp);
        return join("", $tmp);
    }

    protected function mbTrim(string $string, string $characters = " \f\n\r\t\v\x00\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}", ?string $encoding = null): string {
        // On supported versions, use a pre-calculated regex for performance.
        if (PHP_VERSION_ID >= 80200 && ($encoding === null || $encoding === "UTF-8") && $characters === " \f\n\r\t\v\x00\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}") {
            return preg_replace("/^[\s\0]+|[\s\0]+$/uD", '', $string);
        }
    
        try {
            @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('%s(): Argument #3 ($encoding) must be a valid encoding, "%s" given', __FUNCTION__, $encoding));
        }
    
        if ($characters === "") {
            return $string;
        }
    
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, "UTF-8", $encoding);
            $characters = mb_convert_encoding($characters, "UTF-8", $encoding);
        }
    
        $charMap = array_map(static fn(string $char): string => preg_quote($char, '/'), mb_str_split($characters));
        $regexClass = implode('', $charMap);
        $regex = "/^[" . $regexClass . "]+|[" . $regexClass . "]+$/uD";
    
        $return = preg_replace($regex, '', $string);
    
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $return = mb_convert_encoding($return, $encoding, "UTF-8");
        }
    
        return $return;
    }

    protected function mbLtrim(string $string, string $characters = " \f\n\r\t\v\x00\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}", ?string $encoding = null): string {
        // On supported versions, use a pre-calculated regex for performance.
        if (PHP_VERSION_ID >= 80200 && ($encoding === null || $encoding === "UTF-8") && $characters === " \f\n\r\t\v\x00\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}") {
            return preg_replace("/^[\s\0]+/u", '', $string);
        }
    
        try {
            @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('%s(): Argument #3 ($encoding) must be a valid encoding, "%s" given', __FUNCTION__, $encoding));
        }
    
        if ($characters === "") {
            return $string;
        }
    
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, "UTF-8", $encoding);
            $characters = mb_convert_encoding($characters, "UTF-8", $encoding);
        }
    
        $charMap = array_map(static fn(string $char): string => preg_quote($char, '/'), mb_str_split($characters));
        $regexClass = implode('', $charMap);
        $regex = "/^[" . $regexClass . "]+/u";
    
        $return = preg_replace($regex, '', $string);
    
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $return = mb_convert_encoding($return, $encoding, "UTF-8");
        }
    
        return $return;
    }

    protected function mbRtrim(string $string, string $characters = " \f\n\r\t\v\x00\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}", ?string $encoding = null): string {
        // On supported versions, use a pre-calculated regex for performance.
        if (PHP_VERSION_ID >= 80200 && ($encoding === null || $encoding === "UTF-8") && $characters === " \f\n\r\t\v\x00\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}") {
            return preg_replace("/[\s\0]+$/uD", '', $string);
        }
    
        try {
            @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('%s(): Argument #3 ($encoding) must be a valid encoding, "%s" given', __FUNCTION__, $encoding));
        }
    
        if ($characters === "") {
            return $string;
        }
    
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, "UTF-8", $encoding);
            $characters = mb_convert_encoding($characters, "UTF-8", $encoding);
        }
    
        $charMap = array_map(static fn(string $char): string => preg_quote($char, '/'), mb_str_split($characters));
        $regexClass = implode('', $charMap);
        $regex = "/[" . $regexClass . "]+$/uD";
    
        $return = preg_replace($regex, '', $string);
    
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $return = mb_convert_encoding($return, $encoding, "UTF-8");
        }
    
        return $return;
    }
}
