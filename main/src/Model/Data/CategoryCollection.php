<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Data;
use Magento\Catalog\Api\Data\CategoryInterface as MagentoCategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * @internal
 */
class CategoryCollection extends ArrayCollection
{
    /**
     * @param Collection $magentoCollection
     * @return static
     */
    public static function fromMagentoCollection(Collection $magentoCollection)
    {
        return new static($magentoCollection->getIterator()->getArrayCopy());
    }

    /**
     * @return static
     */
    public function filterVisibleInMenu()
    {
        return $this->filter(function (MagentoCategoryInterface $category) {
            return $category->getIsActive() && $category->getIncludeInMenu();
        });
    }

    /**
     * @param $rootCategoryId
     * @return static
     */
    public function filterInRoot($rootCategoryId)
    {
        return $this->filter(function (MagentoCategoryInterface $category) use ($rootCategoryId) {
            $parentIds = \explode('/', $category->getPath());
            return \in_array($rootCategoryId, $parentIds);
        });
    }

    /**
     * @return ArrayCollection
     */
    public function idsWithParents()
    {
        return new ArrayCollection(
            $this
                ->map(function (MagentoCategoryInterface $category) {
                    return \explode('/', $category->getPath());
                })
                ->collapse()
                ->getArrayCopy()
        );
    }
}