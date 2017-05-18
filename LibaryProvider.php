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
    }
}