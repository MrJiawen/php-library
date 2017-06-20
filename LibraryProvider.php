<?php

namespace CjwPhpLibrary;

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
        // 状态码的配置文件
        $StatusConfig = realpath(__DIR__ . '/Src/Config/StatusConfig.php');
        $this->publishes([$StatusConfig => config_path('statusCode.php')]);

        //  项目库的配置文件
        $StatusConfig = realpath(__DIR__ . '/Src/Config/Php_Libary.php');
        $this->publishes([$StatusConfig => config_path('phpLibary.php')]);

        // 阿里云的邮件推送配置文件
        $aliyunPushEmailService = realpath(__DIR__ . '/Src/Service/AliyunPhpEmail/Config/aliyunPushEmailService.php');
        $this->publishes([$aliyunPushEmailService => config_path('aliyunPushEmailService.php')]);
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
