<?php

namespace Jmuffon\Dropbox;

use Illuminate\Support\ServiceProvider;

class DropboxUtilProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this ->publishes([
            __DIR__ . '/config/dropboxutil.php' => config_path( 'dropboxutil.php' ),
        ], 'config' );
    }
}
