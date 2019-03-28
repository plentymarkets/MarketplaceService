<?php

namespace MarketplaceService\Providers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;
use Plenty\Plugin\RouteServiceProvider;

/**
 * Class MarketplaceServiceRouteServiceProvider
 * @package MarketplaceService\Providers
 */
class MarketplaceServiceRouteServiceProvider extends RouteServiceProvider {

    public function register(){}

    /**
     * @param Router $router
     * @param ApiRouter $api
     */
    public function map(Router $router, ApiRouter $api, ConfigRepository $configRepository)
    {
        $api->version(['v1'], ['namespace' => 'MarketplaceService\Controllers'], function ($api) use ($configRepository)
        {
            /** @var ApiRouter $api */
            $api->post($configRepository->get('MarketplaceService.route.web.hook'), 'MarketplacePurchaseController@handlePurchase');
        });
    }
}