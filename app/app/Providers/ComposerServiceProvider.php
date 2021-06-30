<?php namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider {

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
        // View::composer('*', 'App\Http\ViewComposers\ProfileComposer');

        // Using Closure based composers...
        View::composer('global.partials.menu-blade', function($view)
        {
            $view->with('me', Auth::user()->name);
        });

        // Using Closure based composers...
        View::composer('*', function($view)
        {
            $view->with('authUser', Auth::user() );
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
