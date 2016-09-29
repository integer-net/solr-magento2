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

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;

class Item extends AbstractProduct
{
    /**
     * @var ProductImageFactory
     */
    private $productImageFactory;

    public function __construct(Context $context, ProductImageFactory $productImageFactory, array $data)
    {
        $this->productImageFactory = $productImageFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param Product $product
     * @return void
     */
    public function setProduct(Product $product)
    {
        // ImageBuilder::setProduct() expects Magento product model, not ProductInterface
        $this->setData('product', $product);
    }

    /**
     * Get design area. We have to set it here to always be "frontend", since store emulation fails to set the area code in App\State
     * and App\State::emulateAreaCode() does not work together with store emulation
     *
     * @return string
     */
    public function getArea()
    {
        //TODO remove. Using App Emulation now
        return 'frontend';
    }

    /**
     * @return ProductImage
     */
    public function getAutosuggestImage()
    {
        return $this->productImageFactory->create(
            [ProductImage::PARAM_PRODUCT => $this->getProduct()]
        );
    }

}