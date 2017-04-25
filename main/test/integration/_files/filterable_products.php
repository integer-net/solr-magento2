<?php
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;

\call_user_func(function() {
    $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    $entityModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Eav\Model\Entity');
    $entityTypeId = $entityModel->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();

    /** @var \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement */
    $attributeOptionManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Eav\Api\AttributeOptionManagementInterface::class
    );

    $options = [];
    foreach($attributeOptionManagement->getItems($entityTypeId, 'filterable_attribute_a') as $option) {
        $options[$option->getLabel()] = $option->getValue();
    }

    /** @var \Magento\Catalog\Model\Product $product */
    $product = $objectManager->create(
        \Magento\Catalog\Model\Product::class
    );
    $product->isObjectNew(true);
    $product->setSku('product-1')
        ->setId(
            333
        )
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setTypeId(Type::TYPE_SIMPLE)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setUrlKey('product-1-global')
        ->setPrice(10)
        ->setWeight(1)
        ->setTaxClassId(0)
        ->setCategoryIds([2])
        ->setStockData(
            [
                'use_config_manage_stock'   => 1,
                'qty'                       => 100,
                'is_qty_decimal'            => 0,
                'is_in_stock'               => 1,
            ]
        )
        ->setName('Global product name')
        ->setData('filterable_attribute_a', $options['Attribute A Option 1'])
        ->save();

    $product->setStoreId(1)
        ->setName('Product name in store')
        ->setStatus(Status::STATUS_ENABLED)
        ->setUrlKey('product-1-store-1')
        ->save();


    /** @var \Magento\Catalog\Model\Product $product */
    $product = $objectManager->create(
        \Magento\Catalog\Model\Product::class
    );
    $product->isObjectNew(true);
    $product->setSku('product-2')
        ->setId(
            334
        )
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setTypeId(Type::TYPE_SIMPLE)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setUrlKey('product-2-global')
        ->setPrice(10)
        ->setWeight(1)
        ->setTaxClassId(0)
        ->setCategoryIds([2])
        ->setStockData(
            [
                'use_config_manage_stock'   => 1,
                'qty'                       => 100,
                'is_qty_decimal'            => 0,
                'is_in_stock'               => 1,
            ]
        )
        ->setName('Global product name')
        ->setData('filterable_attribute_a', $options['Attribute A Option 2'])
        ->save();

    $product->setStoreId(1)
        ->setName('Product 2 name in store')
        ->setStatus(Status::STATUS_ENABLED)
        ->setUrlKey('product-2-store-1')
        ->save();


    /** @var Category $category */
    $category = $objectManager->create(
        Category::class
    );
    $category->isObjectNew(true);
    $category->setId(
        333
    )->setCreatedAt(
        '2014-06-23 09:50:07'
    )->setName(
        'Category 1'
    )->setParentId(
        2
    )->setPath(
        '1/2/333'
    )->setLevel(
        2
    )->setAvailableSortBy(
        ['position', 'name']
    )->setIsActive(
        true
    )->setPosition(
        1
    )->setIncludeInMenu(
        true
    )->setPostedProducts(
        [333 => 10, 334 => 20]
    )->setDefaultSortBy(
        'name'
    )->save();

});
