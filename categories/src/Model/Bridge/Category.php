<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\SolrCategories\Model\Bridge;

use IntegerNet\SolrCategories\Implementor\Attribute as AttributeInterface;
use IntegerNet\SolrCategories\Implementor\Category as CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface as MagentoCategoryInterface;
use Magento\Catalog\Model\Category as MagentoCategory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class Category implements CategoryInterface
{
    const ABSTRACT_MAX_LENGTH = 100;

    const EVENT_CAN_INDEX_CATEGORY = 'integernet_solr_can_index_category';

    const PARAM_MAGENTO_CATEGORY = 'magentoCategory';
    const PARAM_CATEGORY_PATH_NAMES = 'categoryPathNames';

    /**
     * Magento Category. Only access this directly if methods are needed that are not available in the
     * Service Contract. Prefer {@see getMagentoCategory()} if possible
     *
     * @var MagentoCategory
     */
    private $magentoCategory;
    /**
     * @var string[]
     */
    private $categoryPathNames = [];
    /**
     * @var null|string
     */
    private $description = null;
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param MagentoCategory $magentoCategory
     * @param null|string[] $categoryPathNames
     */
    public function __construct(MagentoCategory $magentoCategory, EventManagerInterface $eventManager,
                                $categoryPathNames = null)
    {
        $this->magentoCategory = $magentoCategory;
        $this->categoryPathNames = $categoryPathNames;
        $this->eventManager = $eventManager;
    }

    public function getId()
    {
        return $this->getMagentoCategory()->getId();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->magentoCategory->getUrl();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getMagentoCategory()->getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (is_null($this->description)) {
            $this->description = $this->magentoCategory->getData('description');
/*            switch ($this->_category->getDisplayMode()) {
                case Mage_Catalog_Model_Category::DM_PAGE:
                case Mage_Catalog_Model_Category::DM_MIXED:
                    if ($blockId = $this->_category->getLandingPage()) {
                        $block = Mage::getModel('cms/block')->load($blockId);
                        if ($block->getId() && $block->getIsActive()) {
                            $this->_description .= ' ' . Mage::helper('cms')->getPageTemplateProcessor()->filter($block->getContent());
                        }
                    }
            }*/
        }
        return $this->description;
    }

    public function getAbstract()
    {
        $content = preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $this->getDescription());
        $content = trim(strip_tags(html_entity_decode($content)));
        if (strlen($content) > self::ABSTRACT_MAX_LENGTH) {
            $content = substr($content, 0, self::ABSTRACT_MAX_LENGTH) . '&hellip;';
        }
        return $content;
    }

    public function getPathExcludingCurrentCategory($separator)
    {
        $pathIds = $this->magentoCategory->getPathIds();
        $pathParts = array();
        array_shift($pathIds);
        array_shift($pathIds);
        array_pop($pathIds);
        foreach($pathIds as $pathId) {
            $pathParts[] = $this->magentoCategory->getResource()->getAttributeRawValue($pathId, 'name', $this->getStoreId());
        }
        return implode($separator, $pathParts);
    }

    public function getStoreId()
    {
        return $this->magentoCategory->getStoreId();
    }

    /**
     * @return int
     */
    public function getSolrId()
    {
        return 'category_' . $this->getId() . '_' . $this->getStoreId();
    }

    /**
     * @return float
     */
    public function getSolrBoost()
    {
        return $this->magentoCategory->getData('solr_boost');
    }

    /**
     * Use first image in content area as page image
     *
     * @return string
     */
    public function getImageUrl()
    {
        if ($imageUrl = $this->magentoCategory->getImageUrl()) {
            // base media URL contains /pub/ if Magento is bootstrapped from CLI, needs to be removed
            return \str_replace('/pub/', '/', $imageUrl);
        }
        $content = $this->getDescription();
        preg_match('/<img.+src=\"(.*)\"/U', $content, $results);
        if (isset($results[1])) {
            // base media URL contains /pub/ if Magento is bootstrapped from CLI, needs to be removed
            return \str_replace('/pub/', '/', $results[1]);
        }
        return '';
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isIndexable($storeId)
    {
        $this->eventManager->dispatch(self::EVENT_CAN_INDEX_CATEGORY, ['category' => $this->magentoCategory]);

        if ($this->magentoCategory->getData('solr_exclude')) {
            return false;
        }

        if (!$this->magentoCategory->getIsActive()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getPath($separator)
    {
        return implode($separator, $this->categoryPathNames);
    }

    /**
     * Returns Magento Category. Use this method to type hint against the Service Contract interface.
     *
     * @return MagentoCategoryInterface
     */
    public function getMagentoCategory()
    {
        return $this->magentoCategory;
    }
}