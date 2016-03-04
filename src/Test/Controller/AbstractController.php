<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

abstract class Integer\Net\Solr\Controller\Test\Controller\AbstractController\AbstractAbstract extends Ecom\Dev\PHPUnit\Test\CaseTest\Controller
{
    protected function setUp()
    {
        parent::setUp();
        $this->app()->getStore(0)->setConfig('integernet_solr/general/install_date', time()-1);
        $installer = new \Magento\Catalog\Model\ResourceModel\Setup('catalog_setup');
        $installer->updateAttribute('catalog_product', 'manufacturer', [
            'is_filterable_in_search' => '1'
        ]);
    }
    protected function tearDown()
    {
        parent::tearDown();
    }

}
