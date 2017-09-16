<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Auth\EloquentUserResolver;
use App\Auth\EloquentClientResolver;
use App\Contracts\UserResolverInterface;
use App\Exceptions\OAuthExceptionHandler;
use App\Contracts\ClientResolverInterface;
use App\Exceptions\UnauthorizedExceptionHandler;
use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerOAuthExceptionHandler();
        $this->app->bind(UserResolverInterface::class, EloquentUserResolver::class);
        $this->app->bind(ClientResolverInterface::class, EloquentClientResolver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    protected function registerOAuthExceptionHandler()
    {
        $handler = $this->app->make('api.exception');
        $handler->register(
            function (OAuthException $exception) {
                return app(OAuthExceptionHandler::class)->handle($exception);
            }
        );
        $handler->register(
            function (UnauthorizedHttpException $exception) {
                return app(UnauthorizedExceptionHandler::class)->handle($exception);
            }
        );
    }
}
