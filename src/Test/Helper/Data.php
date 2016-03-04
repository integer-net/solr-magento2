<?php
use IntegerNet\Solr\Implementor\Attribute;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class Integer\Net\Solr\Test\Helper\Data extends Ecom\Dev\PHPUnit\Test\CaseTest
{
    protected function setUp()
    {
        parent::setUp();

        $colorAttribute = $this->_eavAttribute
            ->setEntityTypeId(\Magento\Catalog\Model\Product::ENTITY)
            ->load('color', 'attribute_code');
        $colorAttribute->setIsFilterableInSearch(true)->save();
    }

    /**
     * @test
     * @helper integernet_solr
     */
    public function shouldGetFilterableInCatalogAttributes()
    {
        $helper = $this->_helperData;
        $actualAttributes = $helper->getFilterableInCatalogAttributes();
        $this->assertInternalType('array', $actualAttributes);
        $this->assertNotEmpty($actualAttributes);
        foreach ($actualAttributes as $actualAttribute) {
            $this->assertInstanceOf(Attribute::class, $actualAttribute);
        }
    }

    /**
     * @test
     * @helper integernet_solr
     */
    public function shouldGetFilterableInSearchAttributes()
    {
        $helper = $this->_helperData;
        $actualAttributes = $helper->getFilterableInSearchAttributes();
        $this->assertInternalType('array', $actualAttributes);
        $this->assertNotEmpty($actualAttributes);
        foreach ($actualAttributes as $actualAttribute) {
            $this->assertInstanceOf(Attribute::class, $actualAttribute);
        }
    }
    /**
     * @test
     * @helper integernet_solr
     */
    public function shouldGetFilterableAttributes()
    {
        $helper = $this->_helperData;
        $actualAttributes = $helper->getFilterableInCatalogOrSearchAttributes();
        $this->assertInternalType('array', $actualAttributes);
        $this->assertNotEmpty($actualAttributes);
        foreach ($actualAttributes as $actualAttribute) {
            $this->assertInstanceOf(Attribute::class, $actualAttribute);
        }
    }
}