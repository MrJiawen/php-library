<?php

namespace CjwLibrary\Src\Service\IpAddress;

use CjwLibrary\Src\Tool\CurlRequest;

/** ip地址查询类别
 * Class IpAddress
 * @package CjwPhpLibary\Src\Service
 */
class IpAddress
{
    public $url;

    public function __construct($type = 'sina')
    {
        $url = [
            'taobao' => 'http://ip.taobao.com/service/getIpInfo.php?ip=213.251.162.44&qq-pf-to=pcqq.group',
            'sina' => 'http://int.dpool.sina.com.cn/iplookup/iplookup.php'
        ];
        $this->url = $url[$type];
    }

    /**ip的归属地查询
     * @param $ip
     * @return mixed
     * @throws \Psy\Exception\ErrorException
     */
    public function ipAddressQuery($ip)
    {
        if (empty($ip))
            simpleError('please set CjwPhpLibary\Src\Tool\Common::ipAddressQuery($ip) param', __FILE__, __LINE__);

        $curl = new CurlRequest();
        $data = [
            'format' => 'json',
            'ip' => $ip
        ];
        $result = $curl->CurlRequest($this->url, $data, 'get');

        $result_data = json_decode($result['data'], true);

        return $result_data;
    }

    /**查询本机的IP地址
     * @return mixed
     */
    public function myIpAddress()
    {

        $curl = new CurlRequest();
        $data = [
            'format' => 'json'
        ];
        $result = $curl->CurlRequest($this->url, $data, 'get');

        $result_data = json_decode($result['data'], true);

        return $result_data;
    }
}
