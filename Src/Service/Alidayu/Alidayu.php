<?php

namespace CjwLibrary\Src\Service\Alidayu;

require_once __DIR__ . '/NewSDK/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use PhpParser\Node\Expr\Array_;

// 加载区域结点配置
Config::load();

class Alidayu
{
    public $config;
    public $SMS;
    public $SMSRequest;

    public function __construct($configKey = 'default')
    {
        //1. 引入阿里大于的SDK
        //include_once __DIR__ . '/SDK/TopSdk.php';

        //2. 加载配置文件
        $this->getConfig($configKey);

        // 3. 初始化发送短信对象
        //$this->SMS = new \TopClient;
        //$this->SMSRequest = new \AlibabaAliqinFcSmsNumSendRequest;

        // 短信API产品名
        $product = "Dysmsapi";

        // 短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $this->config['app_key'], $this->config['app_secret']);

        // 增加服务结点
        DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

        // 初始化AcsClient用于发起请求
        $this->acsClient = new DefaultAcsClient($profile);
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

    /**
     * 发送短信   废弃
     * @param $telephone
     * @param $param            短信的临时变量
     * @return mixed|\ResultSet|\SimpleXMLElement
     */
    private function sendMessage_abandon($telephone, $param)
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

    /**
     * 发送短信范例
     *
     * @param string $signName <p>
     * 必填, 短信签名，应严格"签名名称"填写，参考：<a href="https://dysms.console.aliyun.com/dysms.htm#/sign">短信签名页</a>
     * </p>
     * @param string $templateCode <p>
     * 必填, 短信模板Code，应严格按"模板CODE"填写, 参考：<a href="https://dysms.console.aliyun.com/dysms.htm#/template">短信模板页</a>
     * (e.g. SMS_0001)
     * </p>
     * @param string $phoneNumbers 必填, 短信接收号码 (e.g. 12345678901)
     * @param array|null $templateParam <p>
     * 选填, 假如模板中存在变量需要替换则为必填项 (e.g. Array("code"=>"12345", "product"=>"阿里通信"))
     * </p>
     * @param string|null $outId [optional] 选填, 发送短信流水号 (e.g. 1234)
     * @return stdClass
     */
    private function sendSms($signName, $templateCode, $phoneNumbers, $templateParam = null, $outId = null) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($phoneNumbers);

        // 必填，设置签名名称
        $request->setSignName($signName);

        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数
        if($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }

        // 可选，设置流水号
        if($outId) {
            $request->setOutId($outId);
        }

        // 发起访问请求
        $acsResponse = $this->acsClient->getAcsResponse($request);

        // 打印请求结果
        // var_dump($acsResponse);

        return $acsResponse;

    }

    /**
     * 查询短信发送情况范例
     *
     * @param string $phoneNumbers 必填, 短信接收号码 (e.g. 12345678901)
     * @param string $sendDate 必填，短信发送日期，格式Ymd，支持近30天记录查询 (e.g. 20170710)
     * @param int $pageSize 必填，分页大小
     * @param int $currentPage 必填，当前页码
     * @param string $bizId 选填，短信发送流水号 (e.g. abc123)
     * @return stdClass
     */
    public function queryDetails($phoneNumbers, $sendDate, $pageSize = 10, $currentPage = 1, $bizId=null) {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber($phoneNumbers);

        // 选填，短信发送流水号
        $request->setBizId($bizId);

        // 必填，短信发送日期，支持近30天记录查询，格式Ymd
        $request->setSendDate($sendDate);

        // 必填，分页大小
        $request->setPageSize($pageSize);

        // 必填，当前页码
        $request->setCurrentPage($currentPage);

        // 发起访问请求
        $acsResponse = $this->acsClient->getAcsResponse($request);

        // 打印请求结果
        // var_dump($acsResponse);

        return $acsResponse;
    }

    public function sendMessage($telephone,Array $param){
        $response = $this->sendSms(
            $this->config['sign_name'], // 短信签名
            $this->config['template_code'], // 短信模板编号
            $telephone, // 短信接收者
            $param
        );

        return $response;
    }
}