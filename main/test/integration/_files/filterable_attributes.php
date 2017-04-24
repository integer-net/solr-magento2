<?php
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Setup\CategorySetup');
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$entityModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Eav\Model\Entity');
$entityTypeId = $entityModel->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
$groupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
/** @var \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement */
$attributeOptionManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Eav\Api\AttributeOptionManagementInterface::class
);
/** @var \Magento\Eav\Model\Entity\Attribute\OptionFactory $attributeOptionFactory */
$attributeOptionFactory= \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Eav\Model\Entity\Attribute\OptionFactory::class
);

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$attribute->loadByCode($entityTypeId, 'filterable_attribute_a');
if ($attribute->getId()) {
    $attribute->delete();
}
$attribute->loadByCode($entityTypeId, 'filterable_attribute_b');
if ($attribute->getId()) {
    $attribute->delete();
}

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$attribute->setAttributeCode(
    'filterable_attribute_a'
)->setBackendType('int')
->setFrontendInput('select')
->setEntityTypeId(
    $entityTypeId
)->setAttributeGroupId(
    $groupId
)->setAttributeSetId(
    $attributeSetId
)->setIsFilterable(
    1
)->setIsUserDefined(
    1
)->save();

/** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
$option = $attributeOptionFactory->create();
$option->setLabel('Attribute A Option 1');
$attributeOptionManagement->add($entityTypeId, $attribute->getId(), $option);
$option = $attributeOptionFactory->create();
$option->setLabel('Attribute A Option 2');
$attributeOptionManagement->add($entityTypeId, $attribute->getId(), $option);
$option = $attributeOptionFactory->create();
$option->setLabel('Attribute A Option 3');
$attributeOptionManagement->add($entityTypeId, $attribute->getId(), $option);

$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$attribute->setAttributeCode(
    'filterable_attribute_b'
)->setBackendType('varchar')
->setFrontendInput('multiselect')
->setEntityTypeId(
    $entityTypeId
)->setAttributeGroupId(
    $groupId
)->setAttributeSetId(
    $attributeSetId
)->setIsFilterable(
    1
)->setIsUserDefined(
    1
)->setIsFilterableInSearch(
    1
)->save();
