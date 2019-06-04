<?php

namespace Concrete\Package\CnameProxyIpProvider;

use CNAMEProxyIPProvider\Provider;
use Concrete\Core\Package\Package;
use ProxyIPManager\Provider\ProviderManager;

/**
 * The package controller.
 *
 * Manages the package installation, update and start-up.
 */
class Controller extends Package
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.0';

    /**
     * The unique handle that identifies the package.
     *
     * @var string
     */
    protected $pkgHandle = 'cname_proxy_ip_provider';

    /**
     * The package version.
     *
     * @var string
     */
    protected $pkgVersion = '0.9.0';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$packageDependencies
     */
    protected $packageDependencies = [
        'proxy_ip_manager' => true,
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$pkgAutoloaderRegistries
     */
    protected $pkgAutoloaderRegistries = [
        'src' => 'CNAMEProxyIPProvider',
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('CNAME Proxy IP provider');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Provide the IP addresses of proxies given their CNAME');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::testForInstall()
     */
    public function testForInstall($testForAlreadyInstalled = true)
    {
        $result = parent::testForInstall($testForAlreadyInstalled);
        if (is_object($result) && $result->has()) {
            return $result;
        }
        if ($this->app->make(ProviderManager::class)->getProviderByHandle('cname') === null) {
            return $result;
        }
        $errors = $this->app->make('error');
        $errors->add(t("There's already a Proxy IP Provider registered with the handle \"%s\"", 'cname'));

        return $errors;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        parent::install();
        $this->app->make(ProviderManager::class)->registerProvider('cname', Provider::class, false);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::uninstall()
     */
    public function uninstall()
    {
        parent::uninstall();
        $this->app->make(ProviderManager::class)->unregisterProvider('cname');
    }
}
