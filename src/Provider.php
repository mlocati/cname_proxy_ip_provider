<?php

namespace CNAMEProxyIPProvider;

use ArrayAccess;
use Concrete\Core\Filesystem\ElementManager;
use Concrete\Core\Page\Page;
use Exception;
use ProxyIPManager\Provider\ConfigurableProviderInterface;
use Throwable;

class Provider implements ConfigurableProviderInterface
{
    /**
     * @var \Concrete\Core\Filesystem\ElementManager
     */
    protected $elementManager;

    /**
     * @param \Concrete\Core\Filesystem\ElementManager $elementManager
     */
    public function __construct(ElementManager $elementManager)
    {
        $this->elementManager = $elementManager;
    }

    /**
     * {@inheritdoc}
     *
     * @see \ProxyIPManager\Provider\ProviderInterface::getName()
     */
    public function getName()
    {
        return t('CNAME');
    }

    /**
     * {@inheritdoc}
     *
     * @see \ProxyIPManager\Provider\ProviderInterface::getProxyIPs()
     */
    public function getProxyIPs(ArrayAccess $errors, array $configuration = null)
    {
        $result = [];
        set_error_handler(
            function ($errno, $errstr) use ($errors) {
                $errors[] = $errstr;
            },
            -1
        );
        try {
            foreach ($configuration['hostnames'] as $hostname) {
                $ips = [];
                try {
                    $ips = array_merge($ips, $this->getProxyIPv4For($hostname));
                } catch (Exception $x) {
                    $errors[] = $x->getMessage();
                } catch (Throwable $x) {
                    $errors[] = $x->getMessage();
                }
                try {
                    $ips = array_merge($ips, $this->getProxyIPv6For($hostname));
                } catch (Exception $x) {
                    $errors[] = $x->getMessage();
                } catch (Throwable $x) {
                    $errors[] = $x->getMessage();
                }
                if ($ips === []) {
                    $errors[] = t('No IPv4/IPv6 records found for host name "%s".', $hostname);
                } else {
                    $result = array_merge($result, $ips);
                }
            }
        } finally {
            restore_error_handler();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \ProxyIPManager\Provider\ConfigurableProviderInterface::getDefaultConfiguration()
     */
    public function getDefaultConfiguration()
    {
        return [
            'hostnames' => [
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \ProxyIPManager\Provider\ConfigurableProviderInterface::getConfigurationElement()
     */
    public function getConfigurationElement(array $configuration, Page $page)
    {
        return $this->elementManager->get('configure', 'cname_proxy_ip_provider', $page, ['configuration' => $configuration]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \ProxyIPManager\Provider\ConfigurableProviderInterface::checkConfiguration()
     */
    public function checkConfiguration(array $data, ArrayAccess $errors)
    {
        $hostnames = isset($data['hostnames']) ? $data['hostnames'] : null;
        $hostnames = is_string($hostnames) ? preg_split('/\s+/', $hostnames, -1, PREG_SPLIT_NO_EMPTY) : [];
        if ($hostnames === []) {
            $errors[] = t('Please specify at least one host name.');
        } else {
            $options = [];
            if (defined('FILTER_FLAG_HOSTNAME')) {
                $options['flags'] = FILTER_FLAG_HOSTNAME;
            }
            foreach ($hostnames as $hostname) {
                if (filter_var($hostname, FILTER_VALIDATE_DOMAIN, $options) === false) {
                    $errors[] = t('"%s" is not a valid host name.', $hostname);
                }
            }
        }

        return [
            'hostnames' => $hostnames,
        ];
    }

    /**
     * @param string $hostname
     *
     * @return string[]
     */
    protected function getProxyIPv4For($hostname)
    {
        $ips = gethostbynamel($hostname);

        return empty($ips) ? [] : $ips;
    }

    /**
     * @param string $hostname
     *
     * @return string[]
     */
    protected function getProxyIPv6For($hostname)
    {
        $records = dns_get_record($hostname, DNS_AAAA);
        if (empty($records)) {
            return [];
        }
        $ips = [];
        foreach ($records as $record) {
            $ips[] = $record['ipv6'];
        }

        return $ips;
    }
}
