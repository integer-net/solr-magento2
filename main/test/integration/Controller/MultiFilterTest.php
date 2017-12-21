<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Controller;

use IntegerNet\Solr\Fixtures\SolrConfig;
use IntegerNet\Solr\Model\Indexer\Fulltext as ProductFulltextIndexer;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\TestFramework\ObjectManager;

class MultiFilterTest extends AbstractController
{
    private $options = [];
    const XPATH_PRODUCTS_CONTAINER = '//div[@class="search results"]';

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testUnfilteredResult()
    {
        $this->updateAttributeOptions();

        $this->dispatch('catalogsearch/result/index?q=product');

        $this->assertDomElementPresent(self::XPATH_PRODUCTS_CONTAINER, 'Element with search results should be present');
        $this->assertDomElementContains(self::XPATH_PRODUCTS_CONTAINER, 'Product name in store');
        $this->assertDomElementContains(self::XPATH_PRODUCTS_CONTAINER, 'Product 2 name in store');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testSelectSingleFilterSuccess()
    {
        $this->updateAttributeOptions();

        $this->dispatch('catalogsearch/result/index?q=product&filterable_attribute_a[]=' . $this->options['Attribute A Option 1']);

        $this->assertDomElementPresent(self::XPATH_PRODUCTS_CONTAINER, 'Element with search results should be present');
        $this->assertDomElementContains(self::XPATH_PRODUCTS_CONTAINER, 'Product name in store');
        $this->assertDomElementNotContains(self::XPATH_PRODUCTS_CONTAINER, 'Product 2 name in store');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testSelectMultiFilterSuccess()
    {
        $this->updateAttributeOptions();

        $this->dispatch('catalogsearch/result/index?q=product&filterable_attribute_a[]=' . $this->options['Attribute A Option 1'] . '&filterable_attribute_a[]=' . $this->options['Attribute A Option 3']);

        $this->assertDomElementPresent(self::XPATH_PRODUCTS_CONTAINER, 'Element with search results should be present');
        $this->assertDomElementContains(self::XPATH_PRODUCTS_CONTAINER, 'Product name in store');
        $this->assertDomElementNotContains(self::XPATH_PRODUCTS_CONTAINER, 'Product 2 name in store');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testMultiselectSingleFilterSuccess()
    {
        $this->updateAttributeOptions();

        $this->dispatch('catalogsearch/result/index?q=product&filterable_attribute_b[]=' . $this->options['Attribute B Option 1']);

        $this->assertDomElementPresent(self::XPATH_PRODUCTS_CONTAINER, 'Element with search results should be present');
        $this->assertDomElementContains(self::XPATH_PRODUCTS_CONTAINER, 'Product name in store');
        $this->assertDomElementNotContains(self::XPATH_PRODUCTS_CONTAINER, 'Product 2 name in store');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testMultiselectMultiFilterSuccess()
    {
        $this->updateAttributeOptions();

        $this->dispatch('catalogsearch/result/index?q=product&filterable_attribute_b[]=' . $this->options['Attribute B Option 1'] . '&filterable_attribute_b[]=' . $this->options['Attribute B Option 3']);

        $this->assertDomElementPresent(self::XPATH_PRODUCTS_CONTAINER, 'Element with search results should be present');
        $this->assertDomElementContains(self::XPATH_PRODUCTS_CONTAINER, 'Product name in store');
        $this->assertDomElementNotContains(self::XPATH_PRODUCTS_CONTAINER, 'Product 2 name in store');
    }

    public static function setUpBeforeClass()
    {
        SolrConfig::loadFromConfigFile();

        include __DIR__ . '/../_files/filterable_attributes.php';
        include __DIR__ . '/../_files/filterable_products.php';

        /** @var ProductFulltextIndexer $indexer */
        $indexer = ObjectManager::getInstance()->create(ProductFulltextIndexer::class);
        $indexer->executeFull();
    }

    private function updateAttributeOptions()
    {
        /** @var AttributeOptionManagementInterface $attributeOptionManagement */
        $attributeOptionManagement = $this->objectManager->create(
            AttributeOptionManagementInterface::class
        );

        $entityModel = $this->objectManager->create('Magento\Eav\Model\Entity');
        $entityTypeId = $entityModel->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();

        foreach ($attributeOptionManagement->getItems($entityTypeId, 'filterable_attribute_a') as $option) {
            $this->options[$option->getLabel()] = $option->getValue();
        }

        foreach ($attributeOptionManagement->getItems($entityTypeId, 'filterable_attribute_b') as $option) {
            $this->options[$option->getLabel()] = $option->getValue();
        }
    }

}