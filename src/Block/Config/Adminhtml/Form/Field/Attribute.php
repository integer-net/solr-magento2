<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\Config\Adminhtml\Form\Field;

use IntegerNet\Solr\Implementor\AttributeRepository;
use Magento\Framework\View\Element\Context as ViewElementContext;
use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\StoreManagerInterface;

class Attribute extends Select {
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param AttributeRepository $attributeRepository
     * @param StoreManagerInterface $storeManager
     * @param ViewElementContext $context
     * @param array $data
     */
    public function __construct(AttributeRepository $attributeRepository, StoreManagerInterface $storeManager,
                                ViewElementContext $context, array $data = [])
    {
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }


    public function _toHtml()
    {
        $attributes = $this->attributeRepository
            ->getFilterableInSearchAttributes($this->storeManager->getStore()->getId());

        foreach($attributes as $attribute) {
            $this->addOption($attribute->getAttributeCode(), $attribute->getStoreLabel() . ' [' . $attribute->getAttributeCode() . ']');
        }

        return parent::_toHtml();
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}