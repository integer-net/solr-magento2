<?php
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;

\call_user_func(function() {
    $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

    /** @var \Magento\Catalog\Model\Product $product */
    $product = $objectManager->create(
        \Magento\Catalog\Model\Product::class
    );
    $product->isObjectNew(true);
    $product->setSku('product-1')
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
        ->save();

    $product->setStoreId(1)
        ->setName('Product name in store')
        ->setStatus(Status::STATUS_ENABLED)
        ->setUrlKey('product-1-store-1')
        ->save();
});
