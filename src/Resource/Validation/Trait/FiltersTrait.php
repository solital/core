<?php

namespace Solital\Core\Resource\Validation\Trait;

trait FiltersTrait
{
    use ValidatorsTrait;

    public static $basic_tags = '<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>';
    
    public static $en_noise_words = "about,after,all,also,an,and,another,any,are,as,at,be,because,been,before,
                                     being,between,both,but,by,came,can,come,could,did,do,each,for,from,get,
                                     got,has,had,he,have,her,here,him,himself,his,how,if,in,into,is,it,its,it's,like,
                                     make,many,me,might,more,most,much,must,my,never,now,of,on,only,or,other,
                                     our,out,over,said,same,see,should,since,some,still,such,take,than,that,
                                     the,their,them,then,there,these,they,this,those,through,to,too,under,up,
                                     very,was,way,we,well,were,what,where,which,while,who,with,would,you,your,a,
                                     b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,$,1,2,3,4,5,6,7,8,9,0,_";

    /**
     * Replace noise words in a string (http://tax.cchgroup.com/help/Avoiding_noise_words_in_your_search.htm).
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_noise_words($value, array $params = [])
    {
        $value = preg_replace('/\s\s+/u', chr(32), $value);

        $value = " $value ";

        $words = explode(',', self::$en_noise_words);

        foreach ($words as $word) {
            $word = trim($word);

            $word = " $word "; // Normalize

            if (stripos($value, $word) !== false) {
                $value = str_ireplace($word, chr(32), $value);
            }
        }

        return trim($value);
    }

    /**
     * Remove all known punctuation from a string.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_rmpunctuation($value, array $params = [])
    {
        return preg_replace("/(?![.=$'€%-])\p{P}/u", '', $value);
    }

    /**
     * Sanitize the string by urlencoding characters.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_urlencode($value, array $params = [])
    {
        return filter_var($value, FILTER_SANITIZE_ENCODED);
    }

    /**
     * Sanitize the string by converting HTML characters to their HTML entities.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_htmlencode($value, array $params = [])
    {
        return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Sanitize the string by removing illegal characters from emails.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_email($value, array $params = [])
    {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize the string by removing illegal characters from numbers.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_numbers($value, array $params = [])
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize the string by removing illegal characters from float numbers.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_floats($value, array $params = [])
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }


    /**
     * Sanitize the string by removing any script tags.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_string($value, array $params = [])
    {
        return self::polyfill_filter_var_string($value);
    }

    /**
     * Implemented to replace FILTER_SANITIZE_STRING behaviour deprecated in php8.1
     *
     * @return string
     */
    private static function polyfill_filter_var_string($value)
    {
        $str = preg_replace('/x00|<[^>]*>?/', '', $value);
        return (string)str_replace(['', ''], ['&#39;', '&#34;'], $str);
    }

    /**
     * Converts ['1', 1, 'true', true, 'yes', 'on'] to true, anything else is false ('on' is useful for form checkboxes).
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_boolean($value, array $params = [])
    {
        if (in_array($value, self::$trues, true)) {
            return true;
        }

        return false;
    }

    /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_basic_tags($value, array $params = [])
    {
        return strip_tags($value, self::$basic_tags);
    }

    /**
     * Convert the provided numeric value to a whole number.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_whole_number($value, array $params = [])
    {
        return intval($value);
    }

    /**
     * Convert MS Word special characters to web safe characters. ([“ ”] => ", [‘ ’] => ', [–] => -, […] => ...)
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_ms_word_characters($value, array $params = [])
    {
        return str_replace(['“', '”', '‘', '’', '–', '…'], ['"', '"', "'", "'", '-', '...'], $value);
    }

    /**
     * Converts to lowercase.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_lower_case($value, array $params = [])
    {
        return mb_strtolower($value);
    }

    /**
     * Converts to uppercase.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_upper_case($value, array $params = [])
    {
        return mb_strtoupper($value);
    }

    /**
     * Converts value to url-web-slugs.
     *
     * @see https://stackoverflow.com/questions/40641973/php-to-convert-string-to-slug
     * @see http://cubiq.org/the-perfect-php-clean-url-generator
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_slug($value, array $params = [])
    {
        $delimiter = '-';
        return mb_strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $value))))), $delimiter));
    }
}
