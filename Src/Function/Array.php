<?php

/** 查看元素是否都存在于数组中
 * @param $value
 * @param $array
 * @return bool
 */
function array_value_exists($value, $array)
{
    return array_keys_exists($value, array_flip($value));
}

/**  查看元素是否存在于数组键中
 * @param $value
 * @param $array
 * @return bool
 */
function array_keys_exists($value, $array)
{
    if (is_string($value)) {
        return array_key_exists($value, $array);
    } elseif (is_array($value)) {
        foreach ($value as $val) {
            if (!array_key_exists($val, $array))
                return false;
        }
        return true;
    }
    return false;
}

/** 对象转数组
 * @param $obj
 * @return mixed
 */
function toArray($obj)
{
    return json_decode(json_encode($obj),true);
}
