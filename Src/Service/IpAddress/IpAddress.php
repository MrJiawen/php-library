<?php

namespace CjwPhpLibrary\Src\Service\IpAddress;

use CjwPhpLibrary\Src\Tool\CurlRequest;

/** ip地址查询类别
 * Class IpAddress
 * @package CjwPhpLibary\Src\Service
 */
class IpAddress
{

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
        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php';
        $data = [
            'format' => 'json',
            'ip' => $ip
        ];
        $result = $curl->CurlRequest($url, $data, 'get');

        $result_data = json_decode($result['data'], true);

        return $result_data;
    }

    /**查询本机的IP地址
     * @return mixed
     */
    public function myIpAddress()
    {

        $curl = new CurlRequest();
        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php';
        $data = [
            'format' => 'json'
        ];
        $result = $curl->CurlRequest($url, $data, 'get');

        $result_data = json_decode($result['data'], true);

        return $result_data;
    }
}