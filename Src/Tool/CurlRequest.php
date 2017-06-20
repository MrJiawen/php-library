<?php

namespace CjwPhpLibrary\Src\Tool;

class CurlRequest
{
    //系统成员属性设置
    public $curlobj;                                 //定义curl对象
    public $referer;

    //附加成员属性设置
    public $info;                                    //系统变量变量的设置
    public $cookieDir = '/tmp/myCurlCookie';     //cookie存放的文件夹路径
    public $cookieFile;                            //cookie存放的完整路径


    public function __construct()
    {
        //1. 首先开始创建curl对象
        $this->curlobj = curl_init();

        //2. 使用cookie时候,还有LLs证书认证过期时间,必须设置时区
        date_default_timezone_set('Asia/Shanghai');

        //3. 设置cookie存放的文件夹路径
        if(!empty(config('phpLibrary.curl_request.cookie_dir')))
        $this->cookieDir =  config('phpLibrary.curl_request.cookie_dir');
    }

    //对请求的初始化
    protected function init($param, $callback)
    {
        //1. 判断是否设置输出头信息
        if (empty($param['CURLOPT_HEADER'])) {
            $this->info['CURLOPT_HEADER'] = '本库默认为false,不输出请求头信息';
            curl_setopt($this->curlobj, CURLOPT_HEADER, false);
        } else {
            $this->info['CURLOPT_HEADER'] = '您已经手动设置为true,表示输出请求头信息';
            curl_setopt($this->curlobj, CURLOPT_HEADER, true);
        }

        //2. 设置不直接输出内容
        if (isset($param['CURLOPT_RETURNTRANSFER']) && $param['CURLOPT_RETURNTRANSFER'] === false) {
            $this->info['CURLOPT_RETURNTRANSFER'] = '您已经手动设置为false,表示输出响应内容';
            curl_setopt($this->curlobj, CURLOPT_RETURNTRANSFER, false);
        } else {
            $this->info['CURLOPT_RETURNTRANSFER'] = '本库默认为true,不直接输出响应内容';
            curl_setopt($this->curlobj, CURLOPT_RETURNTRANSFER, true);
        }

        //3.允许跳转重定向
        if (isset($param['CURLOPT_FOLLOWLOCATION']) && $param['CURLOPT_FOLLOWLOCATION'] === false) {
            $this->info['CURLOPT_FOLLOWLOCATION'] = '您已经手动设置为false,表示不允许重定向';
            curl_setopt($this->curlobj, CURLOPT_FOLLOWLOCATION, false);
        } else {
            $this->info['CURLOPT_FOLLOWLOCATION'] = '本库默认为true,允许跳转重定向';
            curl_setopt($this->curlobj, CURLOPT_FOLLOWLOCATION, true);
        }

        //4. 在HTTP请求头中"Referer: "的内容。
        if (empty($param['CURLOPT_REFERER'])) {
            $this->info['CURLOPT_REFERER'] = '本库默认为 空string类型,不设置请求头的referer';
        } else {
            $this->info['CURLOPT_REFERER'] = '您已经手动设置值为' . $param['CURLOPT_REFERER'] . ',表示设置了请求头的referer';
            curl_setopt($this->curlobj, CURLOPT_REFERER, $param['CURLOPT_REFERER']);
        }

        //5. 是否开启cookie
        if (empty($param['COOKE_FLAG'])) {
            $this->info['COOKE_FLAG'] = '本库默认为false,表示不开启cookie文件存储';
        } else {
            $this->info['COOKE_FLAG'] = '您已经手动设置为true,表示开启cookie文件存储';
            $this->initCookie($param);      //初始化cookie
        }

        //6. 执行回调函数,把curl对象做为实参返回,让用户自己设置
        if (!empty($callback)) {
            $callback($this->curlobj, $param);
        }
    }

    //初始化cookie的方法
    protected function initCookie($param)
    {
        //1. 首先判断在/tmp目录下是否有myCurlCookie文件夹
        if (file_exists($this->cookieDir)) {
            if (!is_readable($this->cookieDir) || !is_writable($this->cookieDir)) {
                dd("cookie的文件夹不能读写");
            }
        } else {
            mkdir($this->cookieDir);
        }

        //2. 设置对应的cookie文件名
        if (empty($param['cookieFile'])) {
            $this->cookieFile = tempnam($this->cookieDir, 'mycookie');
            $this->info['cookieFile'] = '本库默认为 空string,表示随机生成一个存储cookie的文件,文件名为' . $this->cookieFile;
        } else {
            if (strpos($param['cookieFile'], $this->cookieDir) === false) dd("自定义cookie存储文件路由请设置到" . $this->cookieDir . '下');

            //赋值,并新建文件,如果存在则无操作
            $this->cookieFile = $param['cookieFile'];
            file_put_contents($this->cookieFile, '', FILE_APPEND);
            $this->info['cookieFile'] = '您已经手动设置为' . $param['cookieFile'] . ',表示cookie内容存储到名为' . $param['cookieFile'] . '的文件中';
        }

        //3. 设置curl选项
        //3.1启用时curl会仅仅传递一个session cookie，忽略其他的cookie
        curl_setopt($this->curlobj, CURLOPT_COOKIESESSION, false);

        //3.2以文件方式,读取cookie加入到req消息头中;
        curl_setopt($this->curlobj, CURLOPT_COOKIEFILE, $this->cookieFile);

        //3.3 以文件存储cookie
        curl_setopt($this->curlobj, CURLOPT_COOKIEJAR, $this->cookieFile);
    }

    //设置请求协议
    protected function setRequstType($type)
    {
        if ($type == 'http') {
        } else if ($type == 'https') {
            curl_setopt($this->curlobj, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($this->curlobj, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        } else {
            dd("请求协议请规范设置,只能为http 或 https");
        }
    }

    //序列化请求参数
    protected function serializeParam($data)
    {
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str = trim($str, '&');
        return $str;
    }

    //判断其请求方式 是 get  还是 post
    protected function setMethod($method)
    {
        if ($method == 'get') {
            curl_setopt($this->curlobj, CURLOPT_HTTPGET, true);           //get  开启
            curl_setopt($this->curlobj, CURLOPT_POST, false);           //post 关闭
            return $method;
        } else if ($method == 'post') {
            curl_setopt($this->curlobj, CURLOPT_POST, true);            //post 开启
            curl_setopt($this->curlobj, CURLOPT_HTTPGET, false);          //get  关闭
            return $method;
        } else {
            dd("请求方式请规范设置,只能为get或post");
        }
    }

    /*
     * GET 数据请求
     *
     * @param           $url        string          请求地址
     * @param           $data       array           请求数据
     * @param           $method     string          请求方式    get 还是post
     * @param           $type       string          请求方式,   ->  http/https
     * @param           $param      array           本库系统curl对象基本参数设置参数
     * @param           $callback   function        回调函数,返回curl对象,其返回的curl对象作为实参对应其回调函数第一个形参------用于配置curl的复杂而又不常用的参数,比如cookie和请求头
     *
     *
     *
     * 扩充:
     *      cookie的设置为  CURLOPT_COOKIE  设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
     *
     *
     *      header请求头的设置
     *
     *          curl_setopt($this->curlobj,CURLOPT_HTTPHEADER,array(
     *              'Content-type: text/plain', 'Content-length: 100'
     *           ));
     *
     * */
    public function CurlRequest($url, $data = array(), $method = 'get', $type = 'http', $param = array(), $callback = null)
    {

        //1. 本库系统curl对象的基本设置
        $this->init($param, $callback);

        //2. 请求方式,是get还是post
        $method = $this->setMethod($method);

        //3. 设置请求类型,是http还是https;
        $this->setRequstType($type);

        //4. 参数序列化
        $dataParam = $this->serializeParam($data);

        //5. 设置请求URL
        if ($method == 'get') {

            curl_setopt($this->curlobj, CURLOPT_URL, $url . '?' . $dataParam);

        } else if ($method == 'post') {

            curl_setopt($this->curlobj, CURLOPT_POSTFIELDS, $dataParam);
            curl_setopt($this->curlobj, CURLOPT_URL, $url);

        }

        //执行
        $output = curl_exec($this->curlobj);

        return ['info' => $this->info, 'data' => $output];
    }

    //关闭资源
    public function close()
    {
        curl_close($this->curlobj);
    }
}