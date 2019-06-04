<?php
/**
 * @var Concrete\Package\CnameProxyIpProvider\Controller\Element\Configure
 * @var Concrete\Core\View\FileLocatorView $this
 * @var Concrete\Core\Form\Service\Form $form
 * @var string $hostnames
 */
?>
<div class="form-group">
    <?= $form->label('hostnames', t('Host names')) ?>
    <?= $form->textarea('hostnames', $hostnames, ['rows' => 7, 'required' => 'required']) ?>
</div>
