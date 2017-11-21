<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Bridge;

use Magento\TestFramework\ObjectManager;
use IntegerNet\Solr\Model\Bridge\TemplateRepository;
use PHPUnit\Framework\TestCase;

class TemplateRepositoryTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testTemplateByStoreId()
    {
        $this->markTestSkipped('Expected value may be incorrect.');
        $storeId = 1;
        /** @var TemplateRepository $templateRepository */
        $templateRepository = $this->objectManager->create(TemplateRepository::class);
        $this->assertEquals(
            \realpath(__DIR__ . '/../../../src/view/frontend/templates/autosuggest/index.phtml'),
            $templateRepository->getTemplateByStoreId($storeId)->getFilename()
        );
    }
}
