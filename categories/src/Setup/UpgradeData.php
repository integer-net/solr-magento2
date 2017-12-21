<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\SolrCategories\Setup;

use Magento\Catalog\Model\Category;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.2.0', '<')) {

            $this->addSolrBoostCategoryAttribute($eavSetup);
        }

        $setup->endSetup();
    }

    /**
     * @param EavSetup $eavSetup
     */
    private function addSolrBoostCategoryAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Category::ENTITY,
            'solr_boost',
            [
                'type'              => 'decimal',
                'input'             => 'text',
                'label'             => 'Solr Priority',
                'frontend_class'    => 'validate-number',
                'note'              => '1 is default, use higher numbers for higher priority.',
                'required'          => 0,
                'user_defined'      => 0,
                'group'             => 'Solr',
                'global'            => 0,
                'visible'           => 1,
                'default'           => 1,
            ]
        );
    }
}