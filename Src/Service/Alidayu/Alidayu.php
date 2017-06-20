<?php

namespace CjwLibrary\Src\Service\Alidayu;

use function GuzzleHttp\Psr7\str;

class Alidayu
{
    public $config;
    public $SMS;
    public $SMSRequest;

    public function __construct($configKey = 'default')
    {
        //1. 引入阿里大于的SDK
        include_once __DIR__ . '/SDK/TopSdk.php';

        //2. 加载配置文件
        $this->getConfig($configKey);

        // 3. 初始化发送短信对象
        $this->SMS = new \TopClient;
        $this->SMSRequest = new \AlibabaAliqinFcSmsNumSendRequest;

    }

    /** 配置阿里大于的配置文件
     * @param $configKey
     */
    private function getConfig($configKey)
    {
        $this->config = config('alidayuSMS');

        if (empty($this->config) || empty($this->config[$configKey]))
            simpleError('please check alidayuSMS config file what is correct !!!  ', __FILE__, __LINE__);

        $this->config = $this->config[$configKey];

        if (empty($this->config['app_key']) ||
            empty($this->config['app_secret']) ||
            empty($this->config['template_code']) ||
            empty($this->config['sign_name']) ||
            empty($this->config['require_param'])
        )
            simpleError('please set alidayuSMS config file what cann\'t be null  !!!  ', __FILE__, __LINE__);
    }

    /** 发送短信
     * @param $telephone
     * @param $param            短信的临时变量
     * @return mixed|\ResultSet|\SimpleXMLElement
     */
    public function sendMessage($telephone, $param)
    {
        //1. 设置公钥私钥
        $this->SMS->appkey = $this->config['app_key'];
        $this->SMS->secretKey = $this->config['app_secret'];


        //2. 配置其他信息
        $this->SMSRequest->setExtend('');   //公共回传参数，在“消息返回”中会透传回该参数；举例：用户可以传入自己下级的会员ID，在消息返回时，该会员ID会包含在内，用户可以根据该会员ID识别是哪位会员使用了你的应用 ==> String可选
        $this->SMSRequest->setSmsType("normal");//短信类型，传入值请填写normal ==> String 必选


        // 3. 配置主要信息
        $this->SMSRequest->setSmsFreeSignName($this->config['sign_name']);//短信签名，传入的短信签名必须是在阿里大于 ==> String 必选

        foreach ($param as $k => $v){
            $param[$k] = (string)$v;
        }
        $this->SMSRequest->setSmsParam(json_encode($param));//短信模板变量，传参规则{"key":"value"}，key的名字须和申请模板中的变量名一致，多个变量之间以逗号隔开。 ==> jsonString    可选

        $this->SMSRequest->setRecNum($telephone);//短信接收号码。支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。
        $this->SMSRequest->setSmsTemplateCode($this->config['template_code']);;//短信模板ID，传入的模板必须是在阿里大于

        // 4. 验证字段是否符合标准
        if (!array_keys_exists($this->config['require_param'], $param))
            simpleError('SMS param isn\'t completing  !!!  ', __FILE__, __LINE__);

        $result = $this->SMS->execute($this->SMSRequest);

        return $result;

    }

}