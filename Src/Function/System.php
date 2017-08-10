<?php

function testSys()
{
    echo "testSys";
}

/** 是否为手机
 * @return bool
 */
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return TRUE;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE;// 找不到为flase,否则为TRUE
    }
    // 判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'mobile', 'nokia', 'sony', 'ericsson', 'mot',
            'samsung', 'htc', 'sgh', 'lg', 'sharp',
            'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo',
            'iphone', 'ipod', 'blackberry', 'meizu', 'android',
            'netfront', 'symbian', 'ucweb', 'windowsce', 'palm',
            'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc',
            'midp', 'wap'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return TRUE;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return TRUE;
        }
    }
    return FALSE;
}

/** 返回的信息
 * @param $code
 * @param null $msg
 */
function statusMsg($code, $msg = null, $data = null)
{
    if (empty($msg)) {
        $responseConfig = Config('statusCode');
        $exist = array_key_exists($code, $responseConfig);

        if (!$exist)
            simpleError('状态码 ' . $code . ' 未定义', __FILE__, __LINE__);

        return ['ServerNo' => $code, 'ServerMsg' => $responseConfig[$code], 'ServerData' => $data];
    }else{
        return ['ServerNo' => $code, 'ServerMsg' => $msg, 'ServerData' => $data];
    }
}

/**
 *  自定义错误异常抛出函数
 * @param $message                              错误信息
 * @param $filename                             文件名
 * @param $lineno                               行号
 * @param int $severity 错误级别    error,warning
 * @param int $code default
 * @param null $previous default
 * @throws \Psy\Exception\ErrorException
 */
function simpleError($message, $filename, $lineno, $severity = 2, $code = 0, $previous = null)
{
    switch ($severity) {
        case 'warning':
            $severity = 2;
            break;
        case 'error':
        default:
            $severity = 1;
    }
    throw new Psy\Exception\ErrorException($message, $code, $severity, $filename, $lineno, $previous);
}

/**
 * 修改或添加请求参数
 * @param array $param
 * @return string
 */
function updateRequestParam(array $param)
{
    // 进行切割requestString
    $uriArray = explode('?', $_SERVER['REQUEST_URI']);

    // 得到请求参数
    array_shift($uriArray);
    $queryString = implode('?', $uriArray);
    parse_str($queryString, $queryString);
    $queryString = array_merge($queryString, $param);

    return http_build_query($queryString);
}

/**
 * 递归删除文件
 * @param $path
 * @param bool $delDir
 * @return bool
 */
function delDirAndFile($path, $delDir = FALSE)
{
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                $result = is_dir("$path/$item") ? delDirAndFile("$path/$item", true) : unlink("$path/$item");
                if (empty($result)) {
                    closedir($handle);
                    return false;
                }
            }
        }
        closedir($handle);

        return $delDir ? rmdir($path) : true;
    } else {

        return file_exists($path) ? unlink($path) : false;
    }
}
