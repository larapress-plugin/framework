<?php

namespace LarapressPlugin\Foundation\Http;

use Exception;
use Throwable;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Support\Facades\Facade;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    /**
     * Handle an incoming HTTP request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request)
    {
        if (defined('ARTISAN_BINARY')) {
            return parent::handle($request);
        }

        $request->enableHttpMethodParameterOverride();
        $this->sendRequestThroughRouter($request);

        return $this;
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendRequestThroughRouter($request)
    {
        if (defined('ARTISAN_BINARY')) {
            return parent::sendRequestThroughRouter($request);
        }

        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        // If administration panel is attempting to be displayed,
        // we don't need any response
        if (is_admin()) {
            return;
        }

        // Get response on `template_include` filter so the conditional functions work correctly
        add_filter('template_include', function ($template) use ($request) {
            try {
                $response = (new Pipeline($this->app))
                        ->send($request)
                        ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                        ->then($this->dispatchToRouter());
            } catch (Exception $e) {
                $this->reportException($e);
                $response = $this->renderException($request, $e);
            } catch (Throwable $e) {
                $this->reportException($e        = new FatalThrowableError($e));
                $response = $this->renderException($request, $e);
            }

            if ($this->checkRequestIsAPI($request)) {
                $this->app['events']->fire('kernel.handled', [$request, $response]);
            } else {
                return $template;
            }
        }, PHP_INT_MAX);
    }

    private function checkRequestIsAPI($request)
    {
        if (empty($request->route()))
            return FALSE;
        $uri           = str_replace('/', '.', $request->route()->uri());
        $uriComponents = explode('.', $uri);
        $pageType      = $uriComponents[0];
        return ($pageType === 'api');
    }

}
