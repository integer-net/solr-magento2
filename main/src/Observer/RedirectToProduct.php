<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedTypeInstance;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableTypeInstance;

class RedirectToProduct implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var GroupedTypeInstance
     */
    private $groupedTypeInstance;
    /**
     * @var ConfigurableTypeInstance
     */
    private $configurableTypeInstance;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RedirectInterface $redirect,
        ProductCollectionFactory $productCollectionFactory,
        GroupedTypeInstance $groupedTypeInstance,
        ConfigurableTypeInstance $configurableTypeInstance
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->redirect = $redirect;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->groupedTypeInstance = $groupedTypeInstance;
        $this->configurableTypeInstance = $configurableTypeInstance;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->scopeConfig->isSetFlag('integernet_solr/general/is_active')) {
            return;
        }

        /** @var RequestInterface $request */
        $request = $observer->getData('request');

        /** @var Action $action */
        $action = $observer->getData('controller_action');

        if ($query = trim($request->getParam('q'))) {
            if (($url = $this->_getProductPageRedirectUrl($query))) {
                $this->redirect->redirect($action->getResponse(), $url);
            }
        }
    }

    /**
     * @param string $query
     * @return string|false;
     */
    private function _getProductPageRedirectUrl($query)
    {
        $matchingProductAttributeCodes = explode(',', $this->scopeConfig->getValue('integernet_solr/results/product_attributes_redirect'));
        if (!sizeof($matchingProductAttributeCodes) || (sizeof($matchingProductAttributeCodes) && !current($matchingProductAttributeCodes))) {
            return false;
        }

        $filters = [];
        foreach ($matchingProductAttributeCodes as $attributeCode) {
            if (!$attributeCode) {
                continue;
            }
            $filters[] = ['attribute' => $attributeCode, 'eq' => $query];
        }

        if (!sizeof($filters)) {
            return false;
        }

        /** @var ProductCollection $matchingProductCollection */
        $matchingProductCollection = $this->productCollectionFactory->create();
        $matchingProductCollection->addStoreFilter();
        $matchingProductCollection->addAttributeToFilter($filters);
        $matchingProductCollection->addAttributeToSelect(['status', 'visibility', 'url_key']);
        $matchingProductCollection->setOrder('visibility', 'desc');

        foreach ($matchingProductCollection as $product) {
            /** @var Product $product */
            if ($url = $this->getProductUrl($product)) {
                return $url;
            }
        }
        return false;
    }

    /**
     * @param Product $product
     * @return string|false
     */
    private function getProductUrl($product)
    {
        if ($product->isVisibleInSiteVisibility()) {
            return $product->getProductUrl();
        }
        if ($product->isComposite()) {
            return false;
        }

        $parentProductIds = [];

        /** @var $groupedTypeInstance GroupedTypeInstance */
        foreach($this->groupedTypeInstance->getParentIdsByChild($product->getId()) as $parentProductId) {
            $parentProductIds[] = $parentProductId;
        }

        /** @var $groupedTypeInstance ConfigurableTypeInstance\ */
        foreach($this->configurableTypeInstance->getParentIdsByChild($product->getId()) as $parentProductId) {
            $parentProductIds[] = $parentProductId;
        }

        /** @var ProductCollection $parentProductCollection */
        $parentProductCollection = $this->productCollectionFactory->create();
        $parentProductCollection->addStoreFilter();
        $parentProductCollection->addWebsiteFilter();
        $parentProductCollection->addIdFilter($parentProductIds);
        $parentProductCollection->addAttributeToSelect(['status', 'visibility', 'url_key']);

        foreach ($parentProductCollection as $parentProduct) {
            /** @var Product $parentProduct */
            if ($productUrl = $this->getProductUrl($parentProduct)) {
                return $productUrl;
            }
        }
        return false;
    }
}