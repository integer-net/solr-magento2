<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Setup;

use IntegerNet\Solr\Model\Entity\Attribute\Source\FilterableProductAttribute as FilterableProductAttributeSource;
use IntegerNet\Solr\Model\Entity\Attribute\Backend\FilterableProductAttribute as FilterableProductAttributeBackend;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
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
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $this->addProductAttributes($eavSetup);
        $this->addCategoryAttributes($eavSetup);
    }

    /**
     * @param EavSetup $eavSetup
     */
    private function addProductAttributes(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            'solr_exclude',
            [
                'type' => 'int',
                'input' => 'select',
                'source' => Boolean::class,
                'label' => 'Exclude this Product from Solr Index',
                'required' => 0,
                'user_defined' => 0,
                'group' => 'Solr',
                'global' => 0,
                'visible' => 1,
                'default' => 0,
                'unique' => 0,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'solr_boost',
            [
                'type' => 'decimal',
                'input' => 'text',
                'label' => 'Solr Priority',
                'frontend_class' => 'validate-number',
                'required' => 0,
                'user_defined' => 1,
                'default' => 1,
                'unique' => 0,
                'note' => '1 is default, use higher numbers for higher priority.',
                'group' => 'Solr',
                'global' => 0,
                'visible' => 1,
            ]
        );
    }

    /**
     * @param EavSetup $eavSetup
     */
    private function addCategoryAttributes(EavSetup $eavSetup)
    {
        $eavSetup->addAttributeGroup(
            Category::ENTITY,
            $eavSetup->getDefaultAttributeSetId(Category::ENTITY),
            'Solr',
            99
        );
        $eavSetup->removeAttribute(Category::ENTITY, 'solr_exclude');
        $eavSetup->removeAttribute(Category::ENTITY, 'solr_exclude_children');
        $eavSetup->removeAttribute(Category::ENTITY, 'solr_remove_filters');
        $eavSetup->addAttribute(
            Category::ENTITY,
            'solr_exclude',
            [
                'type'              => 'int',
                'input'             => 'select',
                'source'            => Boolean::class,
                'label'             => 'Exclude this Category from Solr Index',
                'note'              => 'Exclude only Categories, not included Products',
                'required'          => 0,
                'user_defined'      => 0,
                'group'             => 'Solr',
                'global'            => 0,
                'visible'           => 1,
            ]
        );
        $eavSetup->addAttribute(
            Category::ENTITY,
            'solr_exclude_children',
            [
                'type'              => 'int',
                'input'             => 'select',
                'source'            => Boolean::class,
                'label'             => 'Exclude Child Categories from Solr Index',
                'note'              => 'Exclude only Categories, not included Products',
                'required'          => 0,
                'user_defined'      => 0,
                'group'             => 'Solr',
                'global'            => 0,
                'visible'           => 1,
            ]
        );
        $eavSetup->addAttribute(
            Category::ENTITY,
            'solr_remove_filters',
            [
                'type'              => 'text',
                'input'             => 'multiselect',
                'source'            => FilterableProductAttributeSource::class,
                'backend'           => FilterableProductAttributeBackend::class,
                'label'             => 'Remove Filters',
                'note'              => 'Hold the CTRL key to select multiple filters',
                'required'          => 0,
                'user_defined'      => 0,
                'group'             => 'Solr',
                'global'            => 0,
                'visible'           => 1,
            ]
        );
    }
}