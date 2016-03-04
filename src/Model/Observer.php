<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\Observer
{
    /**
     * Add new field "solr_boost" to attribute form
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function adminhtmlCatalogProductAttributeEditPrepareForm(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $observer->getForm()->getElement('front_fieldset');

        $field = $fieldset->addField('solr_boost', 'text', [
            'name' => 'solr_boost',
            'label' => __('Solr Priority'),
            'title' => __('Solr Priority'),
            'note' => __('1 is default, use higher numbers for higher priority.'),
            'class' => 'validate-number',
        ]);

        // Set default value
        $field->setValue('1.0000');
    }

    /**
     * Add new column "solr_boost" to attribute grid
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function coreBlockAbstractToHtmlBefore(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();

        // Add "Solr Priority" column to attribute grid
        if ($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid) {

            $block->addColumnAfter('solr_boost', [
                'header' => __('Solr Priority'),
                'sortable' => true,
                'index' => 'solr_boost',
                'type' => 'number',
            ], 'is_comparable');
        }

        if ($block instanceof \Magento\Framework\Block\Html\Head) {
            $this->_adjustRobots($block);
        }
    }

    /**
     * Rebuilt Solr Cache on config save
     * Check if cronjobs are active
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function adminSystemConfigChangedSectionIntegernetSolr(\Magento\Framework\Event\Observer $observer)
    {
        $this->_helperAutosuggest->storeSolrConfig();

        if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/connection_check/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return;
        }
        $cronCollection = $this->_scheduleCollection
            ->addFieldToFilter('created_at', ['gt' => Zend_Date::now()->subDay(2)->get(Zend_Date::ISO_8601)]);
        if (!$cronCollection->getSize()) {
            $this->_messageManagerInterface->addWarning(__(
                'It seems you have no cronjobs running. They are needed for doing regular connection checks. We strongly suggest you setup cronjobs. See <a href="http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/how_to_setup_a_cron_job" target="_blank">here</a> for details.'
            ));
        }
    }

    public function controllerActionPredispatchCatalogsearchResultIndex(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && !$this->_getPingResult()) {
            $this->_modelStoreManagerInterface->getStore()->setConfig('integernet_solr/general/is_active', 0);
        }

        /** @var \Magento\Framework\Controller\Magento\Framework\Action $action */
        $action = $observer->getControllerAction();

        if ($this->_helperData->isActive() && $order = $action->getRequest()->getParam('order')) {
            if ($order === 'relevance') {
                $_GET['order'] = 'position';
            }
        }

        $this->_modelStoreManagerInterface->getStore()->setConfig(\Magento\Catalog\Model\Config::XML_PATH_LIST_DEFAULTLIST_SORT_BY, 'position');

        $this->_redirectOnQuery($action);
    }

    public function controllerActionPredispatchCatalogCategoryView(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) 
            && $this->_configScopeConfigInterface->isSetFlag('integernet_solr/category/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) 
            && !$this->_getPingResult()) {
            $this->_modelStoreManagerInterface->getStore()->setConfig('integernet_solr/general/is_active', 0);
        }
        
        if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->_modelStoreManagerInterface->getStore()->setConfig('integernet_solr/category/is_active', 0);
        }

        /** @var \Magento\Framework\Controller\Magento\Framework\Action $action */
        $action = $observer->getControllerAction();

        if ($this->_helperData->isActive() && $order = $action->getRequest()->getParam('order')) {
            if ($order === 'relevance') {
                $_GET['order'] = 'position';
            }
        }

        $this->_modelStoreManagerInterface->getStore()->setConfig(\Magento\Catalog\Model\Config::XML_PATH_LIST_DEFAULTLIST_SORT_BY, 'position');
    }

    /**
     * @return bool
     */
    protected function _getPingResult()
    {
        $solr = $this->_helperFactory->getSolrResource()->getSolrService($this->_modelStoreManagerInterface->getStore()->getId());
        return (boolean)$solr->ping();
    }

    public function catalogProductDeleteAfter(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $indexer \Magento\Indexer\Model\Process */
        $indexer = $this->_modelProcessFactory->create()->load('integernet_solr', 'indexer_code');
        if ($indexer->getMode() != \Magento\Indexer\Model\Process::MODE_REAL_TIME) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getProduct();
            $this->_helperFactory->getProductIndexer()->deleteIndex([$product->getId()]);
        }
    }

    /**
     * Regenerate config if all cache should be deleted.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function applicationCleanCache(\Magento\Framework\Event\Observer $observer)
    {
        $tags = $observer->getTags();
        if (!is_array($tags) || sizeof($tags)) {
            return;
        }
        $this->_helperAutosuggest->storeSolrConfig();
    }

    /**
     * Store Solr configuration in serialized text field so it can be accessed from autosuggest later
     */
    public function storeSolrConfig()
    {
        $this->_helperAutosuggest->storeSolrConfig();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function adminSessionUserLoginSuccess($observer)
    {
        if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return;
        }

        if (!trim($this->_configScopeConfigInterface->getValue('integernet_solr/general/license_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))) {

            if ($installTimestamp = $this->_configScopeConfigInterface->getValue('integernet_solr/general/install_date', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                $diff = time() - $installTimestamp;
                if (($diff < 0) || ($diff > 2419200)) {

                    $this->_messageManagerInterface->addError(
                        __('You haven\'t entered your license key for the IntegerNet_Solr module yet. The module has been disabled automatically.')
                    );

                } else {

                    $this->_messageManagerInterface->addWarning(
                        __('You haven\'t entered your license key for the IntegerNet_Solr module yet. The module will stop working four weeks after installation.')
                    );
                }
            }

        }
    }

    public function checkSolrServerConnection()
    {
        $this->_modelConnectioncheck->checkConnection();
    }

    /**
     * Redirect to product/category page if search query matches one of the configured product/category attributes directly
     *
     * @param \Magento\Framework\App\Action\Action $action
     */
    protected function _redirectOnQuery($action)
    {
        if ($query = trim($action->getRequest()->getParam('q'))) {
            if (($url = $this->_getProductPageRedirectUrl($query)) || ($url = $this->_getCategoryPageRedirectUrl($query))) {
                $action->getResponse()->setRedirect($url);
                $action->getResponse()->sendResponse();
                $action->setFlag($action->getRequest()->getActionName(), \Magento\Framework\Controller\Magento\Framework\Action::FLAG_NO_DISPATCH, true);
            }
        }
    }

    /**
     * @param string $query
     * @return false|string;
     */
    protected function _getProductPageRedirectUrl($query)
    {
        $matchingProductAttributeCodes = explode(',', $this->_configScopeConfigInterface->getValue('integernet_solr/results/product_attributes_redirect', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if (!sizeof($matchingProductAttributeCodes)) {
            return false;
        }
        if (in_array('sku', $matchingProductAttributeCodes)) {
            $product = $this->_modelProductFactory->create();
            if ($productId = $product->getIdBySku($query)) {
                $product->load($productId);
                if ($product->isVisibleInSiteVisibility()) {
                    return $product->getProductUrl();
                }
            }
            $matchingProductAttributeCodes = array_diff($matchingProductAttributeCodes, ['sku']);
        }

        $filters = [];
        foreach ($matchingProductAttributeCodes as $attributeCode) {
            $filters[] = ['attribute' => $attributeCode, 'eq' => $query];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $matchingProductCollection */
        $matchingProductCollection = $this->_productCollection;
        $matchingProductCollection
            ->addStoreFilter()
            ->addAttributeToFilter($filters)
            ->addAttributeToFilter('visibility', ['in' => $this->_productVisibility->getVisibleInSearchIds()])
            ->addAttributeToSelect('url_key');

        if ($matchingProductCollection->getSize() == 1) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $matchingProductCollection->getFirstItem();
            return $product->getProductUrl();
        }
        return false;
    }

    /**
     * @param string $query
     * @return false|string;
     */
    protected function _getCategoryPageRedirectUrl($query)
    {
        $matchingCategoryAttributeCodes = explode(',', $this->_configScopeConfigInterface->getValue('integernet_solr/results/category_attributes_redirect', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if (!sizeof($matchingCategoryAttributeCodes)) {
            return false;
        }
        $filters = [];
        foreach ($matchingCategoryAttributeCodes as $attributeCode) {
            $filters[] = ['attribute' => $attributeCode, 'eq' => $query];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $matchingCategoryCollection */
        $matchingCategoryCollection = $this->_categoryCollection;
        $matchingCategoryCollection
            ->addAttributeToFilter($filters)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect('url_key');

        if ($matchingCategoryCollection->getSize() == 1) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $matchingCategoryCollection->getFirstItem();
            return $category->getUrl();
        }

        return false;
    }

    /**
     * Set Robots to NOINDEX,NOFOLLOW depending on config
     *
     * @param \Magento\Framework\Block\Html\Head $block
     */
    protected function _adjustRobots($block)
    {
        /** @var $helper Integer\Net\Solr\Helper\Data */
        $helper = $this->_helperData;
        if (!$helper->isActive()) {
            return;
        }
        $stateBlock = null;
        $robotOptions = explode(',', $this->_configScopeConfigInterface->getValue('integernet_solr/seo/hide_from_robots', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($helper->isSearchPage()) {
            if (in_array('search_results_all', $robotOptions)) {
                $block->setData('robots', 'NOINDEX,NOFOLLOW');
                return;
            }
            if (!in_array('search_results_filtered', $robotOptions)) {
                return;
            }
            /** @var Integer\Net\Solr\Block\Result\Layer\State $stateBlock */
            $stateBlock = $block->getLayout()->getBlock('catalogsearch.solr.layer.state');
        } elseif ($helper->isCategoryPage() && $helper->isCategoryDisplayActive()) {
            if (!in_array('categories_filtered', $robotOptions)) {
                return;
            }
            /** @var Integer\Net\Solr\Block\Result\Layer\State $stateBlock */
            $stateBlock = $block->getLayout()->getBlock('catalog.solr.layer.state');
        }
        if ($stateBlock instanceof Integer\Net\Solr\Block\Result\Layer\State) {
            $activeFilters = $stateBlock->getActiveFilters();
            if (is_array($activeFilters) && sizeof($activeFilters)) {
                $block->setData('robots', 'NOINDEX,NOFOLLOW');
            }
        }
    }
}