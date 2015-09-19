<?php namespace Arcanedev\Localization\Tests;

use Arcanedev\Localization\Localization;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Class     TestCase
 *
 * @package  Arcanedev\Localization\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var string  */
    protected $defaultLocale    = 'en';

    /** @var array */
    protected $supportedLocales = ['en', 'es', 'fr'];

    /** @var string  */
    protected $testUrlOne       = 'http://localhost/';

    /** @var string  */
    protected $testUrlTwo       = 'http://localhost';

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Package Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get package providers.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Arcanedev\Localization\LocalizationServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            \Arcanedev\Localization\Facades\Localization::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        /**
         * @var  \Illuminate\Config\Repository       $config
         * @var  \Illuminate\Translation\Translator  $translator
         * @var  Localization                        $localization
         */
        $config       = $app['config'];
        $translator   = $app['translator'];
        $localization = $app['arcanedev.localization'];

        $config->set('app.url',    $this->testUrlOne);
        $config->set('app.locale', $this->defaultLocale);
        $config->set('localization.route.middleware', [
            'localization-session-redirect' => false,
            'localization-cookie-redirect'  => false,
            'localization-redirect'         => true,
            'localized-routes'              => true,
        ]);

        $translator->getLoader()->addNamespace(
            'localization',
            realpath(__DIR__) . DS . 'fixtures'. DS .'lang'
        );

        $translator->load('localization', 'routes', 'en');
        $translator->load('localization', 'routes', 'es');
        $translator->load('localization', 'routes', 'fr');

        $localization->setBaseUrl($this->testUrlOne);

        $this->setRoutes();
    }

    /**
     * Refresh routes and refresh application
     *
     * @param  bool|string  $locale
     */
    protected function refreshApplication($locale = false, $session = false)
    {
        parent::refreshApplication();

        app('config')->set('localization.route.middleware', [
            'localization-session-redirect' => $session,
            'localization-cookie-redirect'  => false,
            'localization-redirect'         => true,
            'localized-routes'              => true,
        ]);

        $this->setRoutes($locale);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel',
            Stubs\Http\Kernel::class
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set routes for testing
     *
     * @param  string|bool  $locale
     */
    protected function setRoutes($locale = false)
    {
        if ($locale) {
            localization()->setLocale($locale);
        }

        app('router')->localizedGroup(function () {
            app('router')->get('/', [
                'as'    =>  'index',
                function () {
                    return app('translator')->get('localization::routes.hello');
                }
            ]);

            app('router')->get('test', [
                'as'    => 'test',
                function () {
                    return app('translator')->get('localization::routes.test-text');
                }
            ]);

            app('router')->transGet('localization::routes.about', [
                'as'    => 'about',
                function () {
                    return localization()->getLocalizedURL('es') ? : "Not url available";
                }
            ]);

            app('router')->transGet('localization::routes.view', [
                'as'    => 'view',
                function () {
                    return localization()->getLocalizedURL('es') ? : "Not url available";
                }
            ]);

            app('router')->transGet('localization::routes.view-project', [
                'as'    => 'view-project',
                function () {
                    return localization()->getLocalizedURL('es') ? : "Not url available";
                }
            ]);
        });
    }
}
