<?php
namespace IntegerNet\Solr\Database;

use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Model\Bridge;
use Magento\TestFramework\ObjectManager;

class AttributeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoDataFixture loadFixture
     */
    public function testItReturnsFilterableInSearchAttributes()
    {
        /** @var AttributeRepository $attributeRepository */
        $attributeRepository = $this->objectManager->create(AttributeRepository::class);
        $attributes = $attributeRepository->getFilterableInSearchAttributes(0);
        $foundManufacturer = false;
        foreach ($attributes as $attribute) {
            if ($attribute->getAttributeCode() === 'filterable_attribute_b') {
                $foundManufacturer = true;
                break;
            }
        }
        $this->assertTrue($foundManufacturer, 'Filterable attribute should be in result');
    }

    public static function loadFixture()
    {
        include __DIR__ . '/../_files/filterable_attributes.php';
    }
}