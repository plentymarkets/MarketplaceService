<?php // strict

namespace MarketplaceService\Providers;

use Plenty\Plugin\ServiceProvider;

/**
 * Class MarketplaceServiceProvider
 * @package MarketplaceService\Providers
 */
class MarketplaceServiceProvider extends ServiceProvider
{
    const EVENT_LISTENER_PRIORITY = 105;

    /**
     * Register the core functions
     */
    public function register()
    {
        $this->getApplication()->register(MarketplaceServiceRouteServiceProvider::class);
    }
}
