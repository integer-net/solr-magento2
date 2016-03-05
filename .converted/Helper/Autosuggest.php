<?php
use IntegerNet\Solr\Config\AutosuggestConfig;
use IntegerNet\SolrSuggest\Implementor\SerializableCategoryRepository;
use IntegerNet\SolrSuggest\Implementor\TemplateRepository;
use IntegerNet\SolrSuggest\Plain\Block\Template;
use IntegerNet\SolrSuggest\Plain\Bridge\Category;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Helper\Autosuggest extends \Magento\Framework\App\Helper\AbstractHelper
    implements TemplateRepository, SerializableCategoryRepository
{
    /**
     * @var Integer\Net\Solr\Model\Bridge\StoreEmulation
     */
    protected $_storeEmulation;

    public function __construct(\Integer\Net\Solr\Model\Bridge\StoreemulationFactory $bridgeStoreemulationFactory, 
        \Integer\Net\Solr\Helper\Factory $helperFactory, 
        \Magento\Framework\Filesystem $frameworkFilesystem, 
        \Magento\Framework\View\DesignInterface $viewDesignInterface, 
        \Magento\Framework\App\Config\ScopeConfigInterface $configScopeConfigInterface, 
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection, 
        \Magento\Store\Model\StoreManagerInterface $modelStoreManagerInterface)
    {
        $this->_bridgeStoreemulationFactory = $bridgeStoreemulationFactory;
        $this->_helperFactory = $helperFactory;
        $this->_frameworkFilesystem = $frameworkFilesystem;
        $this->_viewDesignInterface = $viewDesignInterface;
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_categoryCollection = $categoryCollection;
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;

        $this->_storeEmulation = $this->_bridgeStoreemulationFactory->create();
    }


    public function getTemplate()
    {
        return 'integernet/solr/autosuggest.phtml';
    }

    /**
     * Store Solr configuration in serialized text field so it can be accessed from autosuggest later
     */
    public function storeSolrConfig()
    {
        $factory = $this->_helperFactory;
        $factory->getCacheWriter()->write($factory->getStoreConfig());
    }

    /**
     * @param int $storeId
     * @return Template
     */
    public function getTemplateByStoreId($storeId)
    {
        $this->_storeEmulation->start($storeId);
        $template = new Template($this->getTemplateFile($storeId));
        $this->_storeEmulation->stop();
        return $template;
    }


    /**
     * Get absolute path to template
     *
     * @param int $storeId
     * @return string
     */
    public function getTemplateFile($storeId)
    {
        $params = [
            '_relative' => true,
            '_area' => 'frontend',
        ];

        $templateName = $this->_frameworkFilesystem->getDirectoryWrite('app')->getAbsolutePath() . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . $this->_viewDesignInterface->getTemplateFilename($this->getTemplate(), $params);

        $templateContents = file_get_contents($templateName);

        $templateContents = $this->_getTranslatedTemplate($templateContents);

        $targetDirname = $this->_frameworkFilesystem->getDirectoryWrite('cache')->getAbsolutePath() . DIRECTORY_SEPARATOR . 'integernet_solr' . DIRECTORY_SEPARATOR . 'store_' . $storeId;
        if (!is_dir($targetDirname)) {
            mkdir($targetDirname, 0777, true);
        }
        $targetFilename = $targetDirname . DIRECTORY_SEPARATOR . 'autosuggest.phtml';
        file_put_contents($targetFilename, $templateContents);

        return $targetFilename;
    }

    /**
     * @param array $config
     * @param $storeId
     */
    public function _addCategoriesData(&$config, $storeId)
    {
        $maxNumberCategories = intval($this->_configScopeConfigInterface->getValue('integernet_solr/autosuggest/max_number_category_suggestions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if (!$maxNumberCategories) {
            return;
        }

        $categories = $this->_categoryCollection
            ->addAttributeToSelect(['name', 'url_key'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('include_in_menu', 1);

        foreach($categories as $category) {
            $config[$storeId]['categories'][$category->getId()] = [
                'id' => $category->getId(),
                'title' => $this->_getCategoryTitle($category),
                'url' => $category->getUrl(),
            ];
        }
    }



    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    protected function _getCategoryUrl($category)
    {
        $linkType = $this->_configScopeConfigInterface->getValue('integernet_solr/autosuggest/category_link_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (false && $linkType == AutosuggestConfig::CATEGORY_LINK_TYPE_FILTER) {
            return Mage::getUrl('catalogsearch/result', [
                '_query' => [
                    'q' => $this->escapeHtml($this->getQuery()),
                    'cat' => $category->getId()
                ]
            ]);
        }

        return $category->getUrl();
    }

    /**
     * Return category name or complete path, depending on what is configured
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    protected function _getCategoryTitle($category)
    {
        if ($this->_configScopeConfigInterface->isSetFlag('integernet_solr/autosuggest/show_complete_category_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $categoryPathIds = $category->getPathIds();
            array_shift($categoryPathIds);
            array_shift($categoryPathIds);
            array_pop($categoryPathIds);

            $categoryPathNames = [];
            foreach($categoryPathIds as $categoryId) {
                $categoryPathNames[] = Mage::getResourceSingleton('catalog/category')->getAttributeRawValue($categoryId, 'name', $this->_modelStoreManagerInterface->getStore()->getId());
            }
            $categoryPathNames[] = $category->getName();
            return implode(' > ', $categoryPathNames);
        }
        return $category->getName();
    }

    /**
     * Translate all occurences of __('...') with translated text
     *
     * @param string $templateContents
     * @return string
     */
    protected function _getTranslatedTemplate($templateContents)
    {
        preg_match_all('$->__\(\'(.*)\'$', $templateContents, $results);

        foreach($results[1] as $key => $search) {

            $replace = __($search);
            $templateContents = str_replace($search, $replace, $templateContents);
        }

        return $templateContents;
    }

    protected $_configForCache = [];


    /**
     * @param int $storeId
     * @return \IntegerNet\SolrSuggest\Implementor\SerializableCategory[]
     */
    public function findActiveCategories($storeId)
    {
        if (! isset($this->_configForCache[$storeId]['categories'])) {
            $this->_addCategoriesData($this->_configForCache, $storeId);
        }
        return array_map(function(array $categoryConfig) {
            return new Category($categoryConfig['id'], $categoryConfig['title'], $categoryConfig['url']);
        }, $this->_configForCache[$storeId]['categories']);
    }

}