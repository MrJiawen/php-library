<?php

namespace CjwPhpLibrary\Src\Service\AliyunPhpEmail;

use Dm\Request\V20151123 as Dm;

/**阿里云的邮件发送的服务类
 * Class AliyunEmail
 * @package CjwPhpLibary\Src\Service\AliyunPhpEmail
 */
class AliyunEmail
{
    private $config;    //配置信息


    public function __construct()
    {
        // 1. 加载阿里云的SDK文件
        include_once __DIR__ . '/SDK/aliyun-php-sdk-core/Config.php';

        // 2. 获取配置信息
        $this->config = config('aliyunPushEmailService');

        // 3. 检查是否存在未配置的参数
        if (array_value_exists('', $this->config))
            simpleError("please set config/aliyunPushEmailService param,it's not allow null !", __FILE__, __LINE__);
    }

    /** 覆盖配置文件的默认配置
     * @param $param
     * @return $this
     * @author chenjiawen
     */
    public function config($param)
    {
        if (!is_array($param))
            simpleError('it\'s must be array in AliyunEmail::config($param)! ', __FILE__, __LINE__);

        $this->config = array_merge($this->config, $param);

        return $this;
    }

    /** 发送邮件
     * @param $addressee
     * @param $title
     * @param $content
     * @return array
     * @author chenjiawen
     */
    public function send($addressee, $title, $content)
    {
        $iClientProfile = \DefaultProfile::getProfile($this->config['region_id'], $this->config['access_key'], $this->config['access_secret']);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new Dm\SingleSendMailRequest();
        $request->setAccountName($this->config['sender_address']);
        $request->setFromAlias($this->config['sender_alias']);
        $request->setAddressType(1);
        $request->setTagName($this->config['tag_name']);
        $request->setReplyToAddress($this->config['reply_to_address']);
        $request->setToAddress($addressee);
        $request->setSubject($title);
        $request->setHtmlBody($content);
        try {
            $response = $client->getAcsResponse($request);

            return statusMsg('200', null, $response);
        } catch (\ClientException  $e) {

            return statusMsg('400', null, ['error_code' => $e->getErrorCode(), 'error_message' => $e->getErrorMessage()]);

        } catch (\ServerException  $e) {

            return statusMsg('500', null, ['error_code' => $e->getErrorCode(), 'error_message' => $e->getErrorMessage()]);
        }
    }
}

