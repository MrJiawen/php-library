<?php

namespace CjwPhpLibary;

use Illuminate\Support\ServiceProvider;

class LibaryProvider extends ServiceProvider
{
    /**
     *  运行注册后的启动服务器
     *
     * @return void
     */
    public function boot()
    {
        // 状态码的配置文件
        $StatusConfig = realpath(__DIR__ . '/Src/StatusCode/StatusConfig.php');
        $this->publishes([$StatusConfig => config_path('statusCode.php')]);
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