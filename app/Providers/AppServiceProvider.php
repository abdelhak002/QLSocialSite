<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use libphonenumber\PhoneNumberUtil;
use App\Verify\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        // TODO: how we will manage different timezones for different geo-users ??
        if(strtolower(date_default_timezone_get()) !== "africa/algiers")
        {
            date_default_timezone_set("Africa/Algiers");
        }
        // Register Google's Phone-Number-Utility Service
        $this->app->singleton('phoneNumberUtil', function(){
            return PhoneNumberUtil::getInstance();
        });
        // Register Twilio Service
        // $this->app->bind(Service::class, Verification::class);

        $this->app->singleton('country-code-for-client', function(){
            // TODO: set default region using the IP address of the client
            return 'DZ';
        });
        $this->app->singleton('locale-for-client', function(){
            if(!empty(session('locale')))
            {
                // if the user's 'locale' cookie is set we want to use it
                $locale = session('locale');
            }else{
                // most browsers now will send the user's preferred language with the request
                // so we just read it
                $locale = request()->server('HTTP_ACCEPT_LANGUAGE');
                $locale = substr($locale, 0, 2);
            }
            if(in_array($locale, config('app.locales'), true))
            {
                return $locale;
            }
            // if the cookie or the browser's locale is invalid or unknown we fallback
            return config('app.fallback_locale');
        });
        $this->app->singleton('ffprobe', function(){
            return \FFMpeg\FFProbe::create();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // TODO: remove
        if (config('database.log_queries')) {
            DB::connection()->enableQueryLog();
        }
    }
}
