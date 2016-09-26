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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;

class Item extends AbstractProduct
{
    public function setProduct(ProductInterface $product)
    {
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

}