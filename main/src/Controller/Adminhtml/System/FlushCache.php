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

class FlushCache extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'IntegerNet_Solr::config_integernet_solr';
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Action\Context $context, Cache $cache)
    {
        parent::__construct($context);
        $this->cache = $cache;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->cache->regenerate();
        $this->getMessageManager()->addSuccessMessage(__('IntegerNet_Solr configuration cache regenerated'));
        return $this->_redirect('adminhtml/cache/index');
    }


}