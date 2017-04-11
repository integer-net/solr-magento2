<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Attribute as Subject;
use Magento\Framework\App\RequestInterface;

/**
 * Plugin to display multiple filters for same attribute in state block
 */
class CatalogsearchFilterAttributePlugin
{
    /**
     * @var FilterItemFactory
     */
    private $filterItemFactory;

    public function __construct(FilterItemFactory $filterItemFactory)
    {
        $this->filterItemFactory = $filterItemFactory;
    }

    public function aroundApply(Subject $subject, \Closure $proceed, RequestInterface $request)
    {
        $attribute = $subject->getAttributeModel();
        $attributeValue = $request->getParam($attribute->getAttributeCode());
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $attributeValueArrayPart) {
                if (!is_numeric($attributeValueArrayPart)) {
                    return $this;
                }
            }
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $subject->getLayer()
            ->getProductCollection();
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $attributeValueArrayPart) {
                $this->addFilterToState($subject, $attributeValueArrayPart);
            }
        } else {
            $this->addFilterToState($subject, $attributeValue);
        }
        //$this->setItems([]); // set items to disable show filtering
        return $this;
    }

    protected function addFilterToState(Subject $subject, $attributeValue)
    {
        $attribute = $subject->getAttributeModel();

        $label = $attribute->getFrontend()->getOption($attributeValue);
        $subject->getLayer()
            ->getState()
            ->addFilter($this->_createItem($subject, $label, $attributeValue));
    }

    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected function _createItem(Subject $subject, $label, $value, $count = 0)
    {
        return $this->filterItemFactory->create()
            ->setFilter($subject)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }
}
