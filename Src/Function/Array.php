<?php

/** 查看元素是否都存在于数组中
 * @param $value
 * @param $array
 * @return bool
 */
function array_value_exists($value, $array)
{
    return array_keys_exists($value, array_flip($array));
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
    return json_decode(json_encode($obj), true);
}

/** 数组转对象
 * @param $array
 * @return mixed
 */
function toObject($array)
{
    return json_decode(json_encode($array));
}


/** 对json序列化的字符串进行添加或修改某个值
 * @param $key
 * @param $value
 * @param $obj
 * @return string
 */
function jsonString_add($key, $value, $obj)
{
    $obj = json_decode($obj, true);
    $operate = &$obj;
    $key = explode('.', $key);

    for ($i = 0; $i < count($key); $i++) {
        if (isset($operate[$key[$i]]) && is_array($operate[$key[$i]]) && ($i != count($key) - 1)) {
            $operate = &$operate[$key[$i]];
        } else if (!isset($operate[$key[$i]]) && ($i != count($key) - 1)) {
            $operate[$key[$i]] = array();
            $operate = &$operate[$key[$i]];
        } else {
            $operate[$key[$i]] = $value;
        }
    }

    return json_encode($obj);
}

/** 对json序列化的字符串进行删除
 * @param $key
 * @param $obj
 * @return string
 */
function jsonString_del($key, $obj)
{
    $obj = json_decode($obj, true);
    $operate = &$obj;
    $key = explode('.', $key);

    for ($i = 0; $i < count($key); $i++) {
        if (isset($operate[$key[$i]]) && is_array($operate[$key[$i]]) && ($i != count($key) - 1)) {
            $operate = &$operate[$key[$i]];
        } else if (!isset($operate[$key[$i]]) && ($i != count($key) - 1)) {
            simpleError('jsonString_del() param is abnormal，please check "$key" param !!!', __FILE__, __LINE__);
        } else {
            unset($operate[$key[$i]]);
        }
    }

    return json_encode($obj);
}

/** 合并两个对象
 * @param $one
 * @param $two
 * @return mixed
 */
function object_merge($one, $two)
{
    return toObject(array_merge(toArray($one), toArray($two)));
}

