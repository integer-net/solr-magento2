<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/product_associated.php';
require __DIR__ . '/product_virtual_in_stock.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->isObjectNew(true);
$product->setId(22)->setTypeId(
    \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Grouped Product'
)->setSku(
    'grouped-product'
)->setPrice(
    100
)->setTaxClassId(
    0
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
);

$newLinks = [];
$productLinkFactory = $objectManager->get('Magento\Catalog\Api\Data\ProductLinkInterfaceFactory');

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
$productLink = $productLinkFactory->create();
$linkedProduct = $productRepository->getById(1);
$productLink->setSku($product->getSku())
    ->setLinkType('associated')
    ->setLinkedProductSku($linkedProduct->getSku())
    ->setLinkedProductType($linkedProduct->getTypeId())
    ->setPosition(1)
    ->getExtensionAttributes()
    ->setQty(1);
$newLinks[] = $productLink;

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
$productLink = $productLinkFactory->create();
$linkedProduct = $productRepository->getById(21);
$productLink->setSku($product->getSku())
    ->setLinkType('associated')
    ->setLinkedProductSku($linkedProduct->getSku())
    ->setLinkedProductType($linkedProduct->getTypeId())
    ->setPosition(2)
    ->getExtensionAttributes()
    ->setQty(2);
$newLinks[] = $productLink;
$product->setProductLinks($newLinks);
$product->save();
