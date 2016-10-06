<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Controller\Adminhtml\System;

use IntegerNet\Solr\Model\Cache;
use Magento\Backend\App\Action;
use Magento\Framework\App\MutableScopeConfig;

class ConfigureSearchEngine extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'IntegerNet_Solr::config_integernet_solr';
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    public function __construct(Action\Context $context, \Magento\Framework\App\Config\Storage\WriterInterface $configWriter)
    {
        parent::__construct($context);
        $this->configWriter = $configWriter;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->configWriter->save(
            \Magento\CatalogSearch\Model\ResourceModel\EngineInterface::CONFIG_ENGINE_PATH, 'integernet_solr'
        );
        $this->getMessageManager()->addSuccessMessage(__('IntegerNet_Solr configured as search engine'));
        return $this->_redirect('adminhtml/system_config/edit', ['section' => 'integernet_solr']);
    }


}