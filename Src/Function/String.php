<?php

define( "UTF8_CHINESE_PATTERN", "/[\x{4e00}-\x{9fff}\x{f900}-\x{faff}]/u" );
define( "UTF8_SYMBOL_PATTERN", "/[\x{ff00}-\x{ffef}\x{2000}-\x{206F}]/u" );

/** count only chinese words
 * @param string $str
 * @return int
 */
function str_utf8_chinese_word_count($str = ""){
    $str = preg_replace(UTF8_SYMBOL_PATTERN, "", $str);
    return preg_match_all(UTF8_CHINESE_PATTERN, $str, $arr);
}

/** count both chinese and english
 * @param string $str
 * @return int
 */
function str_utf8_mix_word_count($str = ""){
    $str = preg_replace(UTF8_SYMBOL_PATTERN, "", $str);
    return str_utf8_chinese_word_count($str) + str_word_count(preg_replace(UTF8_CHINESE_PATTERN, "", $str));
}

/**
 *  将下划线命名转换为驼峰式命名
 * @param $str
 * @param bool $ucfirst
 * @return mixed|string
 * @author chenjiawen
 */
function convertUnderline ( $str , $ucfirst = true)
{
    $str = ucwords(str_replace('_', ' ', $str));
    $str = str_replace(' ','',lcfirst($str));
    return $ucfirst ? ucfirst($str) : $str;
}

/** tab 转空格
 * @param $tabNum
 * @return string
 */
function tabConvertSpace($tabNum)
{
    return str_repeat(' ', $tabNum * 4);
}

/** 数组转源码字符串
 * @param $arr
 * @return mixed
 */
function arrayToString($arr)
{
    return str_replace('","','", "',str_replace('":"','" => "','['.trim(json_encode($arr),'[]{}').']'));
}
