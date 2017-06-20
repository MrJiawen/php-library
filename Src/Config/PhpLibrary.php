<?php
/**
 * 项目库的配置文件
 */

return [

    // 1. curl类的配置参数
    'curl_request' => [
        // curl对象的cookie存放文件目录，默认:/tmp/myCurlCookie
        'cookie_dir' => ''
    ],


    // 2. 图形验证码 (在session中存储的名称默认为：image_code)
    'image_code' => '',


    // 3. 手机验证码(使用阿里大于)
    'tel_code' => [
        // a. 阿里大于模板变量占位符，默认为code
        'template_variable' => '',

        // b. 存储到redis中的key值（string类型）,默认为tel:code:[手机号]
        'redis_key' => '',

        // c. 有效时长，默认为10分钟
        'redis_time' => '',
    ],


    //4. 邮箱验证码(使用阿里云的邮件推送)
    'email_code' => [
        // a. 邮件发送的标题,默认为"xxx网站验证码"
        'title' => '',

        // b. 邮件发送内容的模板名称,默认为"email_code_default"
        'content'=> '',

        // c. 邮件模板中的变量符，默认为code
        'template_variable' => '',

        // d. 存储到redis中的key值（string类型）,默认为email:code:[邮箱账号]
        'redis_key' => '',

        // e. 有效时长，默认为10分钟
        'redis_time' => '',
    ]
];
