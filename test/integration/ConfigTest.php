<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr;


use Magento\TestFramework\TestCase\AbstractBackendController;

class ConfigTest extends  AbstractBackendController
{

    protected function setUp()
    {
        parent::setUp();
        $this->uri = 'backend/admin/system_config/edit';
        $this->resource = 'IntegerNet_Solr::config_integernet_solr';
        $this->getRequest()->setParam('section', 'integernet_solr');
    }

    /**
     * Overridden to make depends annotation work
     */
    public function testAclHasAccess()
    {
        parent::testAclHasAccess();
    }

    /**
     * Overridden to check for redirect instead of "Forbidden" response
     */
    public function testAclNoAccess()
    {
        if ($this->resource === null) {
            $this->markTestIncomplete('Acl test is not complete');
        }
        $this->_objectManager->get('Magento\Framework\Acl\Builder')
            ->getAcl()
            ->deny(null, $this->resource);
        $this->dispatch($this->uri);
        $this->assertSame(302, $this->getResponse()->getHttpResponseCode());
        $this->assertContains('/index.php/backend/admin/system_config/index/', $this->getResponse()->getHeader('location')->toString());
    }


    /**
     * @depends testAclHasAccess
     */
    public function testConfigSectionLoads()
    {
        $this->assertEquals(200, $this->getResponse()->getStatusCode(), 'HTTP Status Code');
        //TODO test rendered status block
    }
}
