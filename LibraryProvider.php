<?php

namespace CjwLibrary;

use Illuminate\Support\ServiceProvider;

class LibraryProvider extends ServiceProvider
{
    /**
     *  运行注册后的启动服务器
     *
     * @return void
     */
    public function boot()
    {
        // 1. 状态码的配置文件
        $StatusConfig = realpath(__DIR__ . '/Src/Config/StatusConfig.php');
        $this->publishes([$StatusConfig => config_path('statusCode.php')]);

        // 2. 项目库的配置文件
        $StatusConfig = realpath(__DIR__ . '/Src/Config/PhpLibrary.php');
        $this->publishes([$StatusConfig => config_path('phpLibrary.php')]);

        // 3. 阿里云的邮件推送配置文件
        $aliyunPushEmailService = realpath(__DIR__ . '/Src/Service/AliyunPhpEmail/Config/aliyunPushEmailService.php');
        $this->publishes([$aliyunPushEmailService => config_path('aliyunPushEmailService.php')]);

        // 4. 阿里大于的配置文件
        $StatusConfig = realpath(__DIR__ . '/Src/Service/Alidayu/Config/AlidayuSMS.php');
        $this->publishes([$StatusConfig => config_path('alidayuSMS.php')]);

        // 5. 阿里云的邮件验证码模板
        $aliyunEmailCodeTemplate = realpath(__DIR__.'/Src/Template/email_code_default.blade.php');
        $this->publishes([$aliyunEmailCodeTemplate => base_path('resources/views/vendor/email_code/email_code_default.blade.php')]);
    }

    /**
     *  在容器中注册绑定
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
        $this->app->singleton(Src\Tool\CommonTool::class,Src\Tool\CommonTool::class);
        $this->app->singleton(Src\Tool\CurlRequest::class,Src\Tool\CurlRequest::class);
    }
}
