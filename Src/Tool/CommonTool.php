<?php

namespace CjwPhpLibrary\Src\Tool;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Ramsey\Uuid\Uuid;

/** 通用的工具类
 * Class CommonTool
 * @package CjwPhpLibary\Src\Tool
 */
class CommonTool
{
    protected $identifyingCode = 'identifying_code';

    public function __construct()
    {

    }

    /** 验证码的生成器
     * @param int $width
     * @param int $height
     * @param null $filename
     */
    public function identifyingCodeGenerator($width = 100, $height = 40, $filename = null)
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
    public function identifyingCodeVerification($identifyingCode)
    {
        $sysIdentifying = \Session::get($this->identifyingCode);
        if ($identifyingCode == null) return false;
        return $identifyingCode == $sysIdentifying ? true : false;
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