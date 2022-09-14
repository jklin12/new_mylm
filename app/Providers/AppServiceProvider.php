<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Paginator::useBootstrapFour();
        Blade::directive('currency', function ($expression) {
            return "Rp. <?php echo number_format($expression,0,',','.'); ?>";
        });

        Blade::directive('precentage', function ($expression) {
            $array = json_decode($expression, true);
            print_r($array);
            die;
            return "<?php echo (($array[0]/$array[1])*100) ?>";
        });
        Validator::extend('phone_number', function ($attribute, $value, $parameters) {
            return substr($value, 0, 2) == '62';
        });
    }
}
