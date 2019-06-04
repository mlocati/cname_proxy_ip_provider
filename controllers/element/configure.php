<?php

namespace Concrete\Package\CnameProxyIpProvider\Controller\Element;

use Concrete\Core\Controller\ElementController;

class Configure extends ElementController
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        parent::__construct();
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\ElementController::getElement()
     */
    public function getElement()
    {
        return 'configure';
    }

    public function view()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('hostnames', implode("\n", $this->configuration['hostnames']));
    }
}
