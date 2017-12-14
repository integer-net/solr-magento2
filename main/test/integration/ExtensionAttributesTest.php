<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr;

use Magento\Catalog\Api\Data\CategoryExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ExtensionAttributesTest extends TestCase
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->extensionAttributesFactory = $this->objectManager->get(ExtensionAttributesFactory::class);
    }

    /**
     * @test
     * @dataProvider dataExtensionAttributes
     * @param $class
     * @param $attributeGetter
     */
    public function testExtensionAttributes($class, $attributeGetter)
    {
        $productExtensionAttributes = $this->extensionAttributesFactory->create($class);
        $this->assertTrue(method_exists($productExtensionAttributes, $attributeGetter), sprintf('Method %s should exist', $attributeGetter));
    }
    public static function dataExtensionAttributes()
    {
        return [
            [
                'class' => ProductInterface::class,
                'attribute_getter' => 'getSolrExclude',
            ],
            [
                'class' => ProductInterface::class,
                'attribute_getter' => 'getSolrBoost',
            ],
            [
                'class' => CategoryInterface::class,
                'attribute_getter' => 'getSolrExclude',
            ],
            [
                'class' => CategoryInterface::class,
                'attribute_getter' => 'getSolrExcludeChildren',
            ],
            [
                'class' => CategoryInterface::class,
                'attribute_getter' => 'getSolrRemoveFilters',
            ],
        ];
    }

}