<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Block\Autosuggest;


use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;

class ProductImage
{
    /**
     * Image ID as defined in view.xml
     */
    const IMAGE_ID = 'integernet_solr_autosuggest_image';

    /**
     * @var Product
     */
    private $product;
    /**
     * @var ImageBuilder
     */
    private $imageBuilder;
    /**
     * @var Image
     */
    private $image;

    /**
     * Dynamic constructor parameter
     */
    const PARAM_PRODUCT = 'product';

    public function __construct(ImageBuilder $imageBuilder, Product $product)
    {
        $this->imageBuilder = $imageBuilder;
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function title()
    {
        return $this->imageInstance()->getLabel();
    }

    /**
     * @return string
     */
    public function imgHtml()
    {
        // base media URL contains /pub/ if Magento is bootstrapped from CLI, needs to be removed
        return \str_replace('/pub/', '/', $this->imageInstance()->toHtml());
    }

    /**
     * @return Image
     */
    private function imageInstance()
    {
        if ($this->image === null) {
            $this->image = $this->imageBuilder
                ->setProduct($this->product)
                ->setImageId(static::IMAGE_ID)
                ->create();
        }
        return $this->image;
    }

}