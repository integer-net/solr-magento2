<?php
use Magento\Catalog\Model\Category;

\call_user_func(function() {
    $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

    $store = $objectManager->create('Magento\Store\Model\Store');
    $secondStoreId = $store->load('fixture_second_store', 'code')->getId();

    /** @var \Magento\Framework\Registry $registry */
    $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', true);

    /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
    $collection = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
    $collection->addFieldToFilter('level', ['gt' => 1])->delete();

    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', false);

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
    )->setIsActive(
        true
    )->setIncludeInMenu(
        true
    )->save();
    $category->setStoreId($secondStoreId)->save();

    $category = $objectManager->create(
        Category::class
    );
    $category->isObjectNew(true);
    $category->setId(
        444
    )->setCreatedAt(
        '2015-07-24 10:55:10'
    )->setName(
        'Category 2'
    )->setParentId(
        2
    )->setPath(
        '1/2/444'
    )->setLevel(
        2
    )->setIsActive(
        true
    )->setIncludeInMenu(
        true
    )->save();
    $category->setStoreId($secondStoreId)->save();

    $category = $objectManager->create(
        Category::class
    );
    $category->isObjectNew(true);
    $category->setId(
        555
    )->setCreatedAt(
        '2016-08-25 12:13:14'
    )->setName(
        'Category 3'
    )->setParentId(
        2
    )->setPath(
        '1/2/555'
    )->setLevel(
        2
    )->setIsActive(
        false
    )->setIncludeInMenu(
        true
    )->save();
    $category->setStoreId($secondStoreId)->save();

    $category = $objectManager->create(
        Category::class
    );
    $category->isObjectNew(true);
    $category->setId(
        666
    )->setCreatedAt(
        '2016-09-24 11:11:11'
    )->setName(
        'Category 4'
    )->setParentId(
        2
    )->setPath(
        '1/2/666'
    )->setLevel(
        2
    )->setIsActive(
        true
    )->setIncludeInMenu(
        false
    )->save();
    $category->setStoreId($secondStoreId)->save();
});