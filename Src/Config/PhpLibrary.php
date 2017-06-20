<?php
/**
 * 项目库的配置文件
 */

return [

    // curl类的配置参数
    'curl_request' => [
        // curl对象的cookie存放文件目录，默认:/tmp/myCurlCookie
        'cookie_dir' => ''
    ],

    // 图形验证码 (在session中存储的名称默认为：image_code)
    'image_code' => '',
    
];
