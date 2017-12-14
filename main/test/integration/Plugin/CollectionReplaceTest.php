<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Plugin;

use IntegerNet\Solr\Model\Plugin\CollectionProviderPlugin;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CollectionReplaceTest extends TestCase
{

    public function testTheCollectionProviderPluginIsRegistered()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();

        /** @var PluginList $pluginList */
        $pluginList = $objectManager->create(PluginList::class);

        $pluginInfo = $pluginList->get(ItemCollectionProviderInterface::class, []);
        $this->assertSame(CollectionProviderPlugin::class, $pluginInfo['replaceCollections']['instance']);
    }
}