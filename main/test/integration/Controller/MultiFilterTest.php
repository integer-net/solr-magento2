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

use IntegerNet\Solr\Model\Indexer\Fulltext as ProductFulltextIndexer;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\TestFramework\ObjectManager;

class MultiFilterTest extends \Magento\TestFramework\TestCase\AbstractController
{
    private $options = [];

    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        /** @var AttributeOptionManagementInterface $attributeOptionManagement */
        $attributeOptionManagement = $this->objectManager->create(
            AttributeOptionManagementInterface::class
        );

        $entityModel = $this->objectManager->create('Magento\Eav\Model\Entity');
        $entityTypeId = $entityModel->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();

        foreach($attributeOptionManagement->getItems($entityTypeId, 'filterable_attribute_a') as $option) {
            $this->options[$option->getLabel()] = $option->getValue();
        }
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testUnfilteredResult()
    {
        $this->dispatch('catalogsearch/result/index?q=product');

        $this->assertContains('Product name in store', $this->getResponse()->getBody());
        $this->assertContains('Product 2 name in store', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSingleFilterSuccess()
    {
        $this->dispatch('catalogsearch/result/index?q=product&filterable_attribute_a[]=' . $this->options['Attribute A Option 1']);

        $this->assertContains('Product name in store', $this->getResponse()->getBody());
        $this->assertNotContains('Product 2 name in store', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testMultiFilterSuccess()
    {
        $this->dispatch('catalogsearch/result/index?q=product&filterable_attribute_a[]=' . $this->options['Attribute A Option 1'] . '&filterable_attribute_a[]=' . $this->options['Attribute A Option 3']);

        $this->assertContains('Product name in store', $this->getResponse()->getBody());
        $this->assertNotContains('Product 2 name in store', $this->getResponse()->getBody());
    }

    public static function loadFixture()
    {
        if (file_exists(__DIR__ . '/../_files/solr_config.php')) {
            include __DIR__ . '/../_files/solr_config.php';
        } else {
            include __DIR__ . '/../_files/solr_config.dist.php';
        }

        include __DIR__ . '/../_files/filterable_attributes.php';
        include __DIR__ . '/../_files/filterable_products.php';

        /** @var ProductFulltextIndexer $indexer */
        $indexer = ObjectManager::getInstance()->create(ProductFulltextIndexer::class);
        $indexer->executeFull();
    }
}