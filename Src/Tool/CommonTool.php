<?php

namespace CjwLibrary\Src\Tool;

use CjwLibrary\Src\Service\Alidayu\Alidayu;
use CjwLibrary\Src\Service\AliyunPhpEmail\AliyunEmail;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

/** 通用的工具类
 * Class CommonTool
 * @package CjwPhpLibary\Src\Tool
 */
class CommonTool
{
    protected $identifyingCode;

    public function __construct()
    {
        $image_code = config('phpLibrary.image_code');
        $this->identifyingCode = empty($image_code) ? 'image_code' : $image_code;
    }

    /** 验证码的生成器
     * @param int $width
     * @param int $height
     * @param null $filename
     */
    public function imageCodeGenerator($width = 100, $height = 40, $filename = null)
    {
        $phrase = new PhraseBuilder();
        $code = $phrase->build(4);
        //生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder($code, $phrase);
        //设置背景颜色
        $builder->setBackgroundColor(220, 220, 220);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        //可以设置图片宽高及字体
        $builder->build($width, $height, null);
        // $builder->build($width = 100, $height = 40, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();

        //把内容存入session
        \Session::flash($this->identifyingCode, $phrase);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        if (empty($filename)) {
            $builder->output();
        } else {
            $builder->save($filename);
        }
    }

    /** 验证码的验证器
     * @param $identifyingCode
     * @return bool
     */
    public function imageCodeVerification($identifyingCode)
    {
        $sysIdentifying = \Session::get($this->identifyingCode);
        if ($identifyingCode == null) return false;
        return $identifyingCode == $sysIdentifying ? true : false;
    }

    /** 手机验证码的生成器
     * @param $telephone
     * @param string $SMSConfig
     * @return array
     */
    public function telephoneCodeGenerator($telephone, $SMSConfig = 'default')
    {
        // 1. 准备参数
        $code = $number = rand(100000, 999999);

        $templateVariable = config('phpLibrary.tel_code.template_variable');
        $templateVariable = empty($templateVariable) ? 'code' : $templateVariable;

        $redisKey = config('phpLibrary.tel_code.redis_key');
        $redisKey = empty($redisKey) ? 'tel:code:' : $redisKey;

        $redisTime = config('phpLibrary.tel_code.redis_time');
        $redisTime = empty($redisTime) ? 600 : $redisTime;

        // 2. 发送短信
        $alidayu = new Alidayu($SMSConfig);
        $result = $alidayu->sendMessage($telephone, [$templateVariable => $code]);

        if (!empty($result->result) &&
            !empty($result->result->code) &&
            $result->result->code == "OK"
        ) {
            // 3. 存入缓存
            Redis::setEx($redisKey . $telephone, $redisTime, $code);

            return statusMsg(200, '短信发送成功', $result);
        } else {
            return statusMsg(500, '短信发送失败', $result);
        }
    }

    /** 手机验证码的验证器(验证一次则被清空)
     * @param $telephone
     * @param $code
     * @return array
     */
    public function telephoneCodeVerification($telephone, $code)
    {
        //1. 准备参数
        $redisKey = config('phpLibrary.tel_code.redis_key');
        $redisKey = empty($redisKey) ? 'tel:code:' : $redisKey;

        // 2. 获取redis中数据
        $result = Redis::get($redisKey . $telephone);

        if (empty($result) || $result != $code) {
            return statusMsg(400, '手机验证码验证失败', ['redis_value' => $result, 'code' => $code]);
        }
        Redis::del($redisKey . $telephone);
        return statusMsg(200, '手机验证码验证成功', ['redis_value' => $result]);
    }


    /** 邮箱验证码的生成器
     * @param $email
     * @param string $driver
     * @return array
     */
    public function emailCodeGenerator($email, $driver = 'aliyun_email')
    {
        // 1. 准备参数
        $code = $number = rand(100000, 999999);

        $title = config('phpLibrary.email_code.title');
        $title = empty($title) ? "xxx网站验证码" : $title;

        $content = config('phpLibrary.email_code.content');
        $content = empty($content) ? 'email_code_default' : $content;

        $templateVariable = config('phpLibrary.email_code.template_variable');
        $templateVariable = empty($templateVariable) ? 'code' : $templateVariable;

        $redisKey = config('phpLibrary.email_code.redis_key');
        $redisKey = empty($redisKey) ? 'email:code:' : $redisKey;

        $redisTime = config('phpLibrary.email_code.redis_time');
        $redisTime = empty($redisTime) ? 600 : $redisTime;

        // 2. 发送邮件
        $result = [];
        if ($driver == 'aliyun_email') {
            $aliyunEmail = new AliyunEmail();

            $result = $aliyunEmail->send($email, $title, (string)view('vendor.email_code.' . $content, [$templateVariable => $code]));

        } else {
            simpleError('please select email driver !!! ', __FILE__, __LINE__);
        }

        // 3. 返回结果
        if ($result['ServerNo'] == 200) {
            // 3. 存入缓存
            Redis::setEx($redisKey . $email, $redisTime, $code);
        }
        return $result;
    }

    /** 邮箱验证码的验证器(验证一次则被清空)
     * @param $email
     * @param $code
     * @return array
     */
    public function emailCodeVerification($email, $code)
    {
        //1. 准备参数
        $redisKey = config('phpLibrary.email_code.redis_key');
        $redisKey = empty($redisKey) ? 'email:code:' : $redisKey;

        // 2. 获取redis中数据
        $result = Redis::get($redisKey . $email);

        if (empty($result) || $result != $code) {
            return statusMsg(400, '邮箱验证码验证失败', ['redis_value' => $result, 'code' => $code]);
        }
        Redis::del($redisKey . $email);
        return statusMsg(200, '邮箱验证码验证成功', ['redis_value' => $result]);
    }

    /** 生成uuid
     * @return mixed
     */
    public function getUuid()
    {
        $temp = Uuid::uuid4();
        $uuid = $temp->getHex();#uuid

        return $uuid;
    }

    /**cookie的生成器
     * @param $key
     * @param $value
     */
    public function cookieGenerator($key, $value)
    {
        setcookie($key, $value, time() + 9999999999, '/');
    }

    /** cookie的验证器
     * @param $key
     * @param $value
     * @return bool
     */
    public function cookieVerification($key, $value)
    {
        if (!empty($_COOKIE[$key]) && ($value == $_COOKIE[$key]))
            return true;

        return false;
    }

}