<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\SolrCategories\Controller;

use IntegerNet\Solr\Controller\AbstractController;
use IntegerNet\Solr\Fixtures\SolrConfig;
use IntegerNet\SolrCategories\Model\Indexer\Fulltext as FulltextIndexer;
use TddWizard\Fixtures\Catalog\CategoryBuilder;
use TddWizard\Fixtures\Catalog\CategoryFixture;
use TddWizard\Fixtures\Catalog\CategoryFixtureRollback;

class ResultTest extends AbstractController
{
    const SEARCH_RESULT_XPATH   = '//div[@id="solr_tab_content_categories"]';

    /**
     * @var CategoryFixture[]
     */
    private $categories = [];

    protected function setUp()
    {
        $this->categories = [];
        parent::setUp();
        SolrConfig::loadAdditional(['integernet_solr/category/is_indexer_active' => 1]);
        $this->createCategories();
        $this->executeFullIndex();
    }

    protected function tearDown()
    {
        CategoryFixtureRollback::create()->execute(...$this->categories);
        parent::tearDown();
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testNoCategoriesOnSearchResultPage()
    {
        $this->dispatch('catalogsearch/result/index?q=' . $this->getNotMatchingSearchTerm());

        $this->assertCategoryResultTabNotPresent();
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testCategoriesOnSearchResultPage()
    {
        $this->dispatch('catalogsearch/result/index?q=' . $this->getMatchingSearchTerm());

        $this->assertCategoryResultTabPresent();
        $this->assertCategoryInResult($this->findMatchingCategory(), 'Active matching Category');
        $this->assertCategoryNotInResult($this->findInactiveMatchingCategory(), 'Inactive matching category');
        $this->assertCategoryNotInResult($this->findNonMatchingCategory(), 'Non-matching category');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testFuzzyCategoriesOnSearchResultPage()
    {
        $this->dispatch('catalogsearch/result/index?q=' . $this->getFuzzyMatchingSearchTerm());

        $this->assertCategoryResultTabPresent();
        $this->assertCategoryInResult($this->findMatchingCategory(), 'Fuzzy matching Category');
    }

    private function executeFullIndex()
    {
        /** @var FulltextIndexer $indexer */
        $indexer = $this->objectManager->create(FulltextIndexer::class);
        $indexer->executeFull();
    }

    private function createCategories()
    {
        $category = CategoryBuilder::topLevelCategory();
        $this->createCategoryFixtureFromBuilders(
            $category->withDescription('Matching ' . $this->getMatchingSearchTerm()),
            $category->withDescription('Another matching' . $this->getMatchingSearchTerm()),
            $category->withDescription('Inactive matching ' . $this->getMatchingSearchTerm())->withIsActive(false),
            $category->withDescription('Not matching')
        );
    }

    private function createCategoryFixtureFromBuilders(CategoryBuilder ...$builders)
    {
        foreach ($builders as $builder) {
            $this->categories[] = new CategoryFixture($builder->build());
        }
    }

    private function findMatchingCategory() : CategoryFixture
    {
        return $this->categories[0];
    }

    private function findInactiveMatchingCategory() : CategoryFixture
    {
        return $this->categories[2];
    }

    private function findNonMatchingCategory() : CategoryFixture
    {
        return $this->categories[3];
    }

    private function getNotMatchingSearchTerm()
    {
        return 'abcdefg';
    }

    private function getMatchingSearchTerm()
    {
        return 'gesundheit';
    }

    private function getFuzzyMatchingSearchTerm()
    {
        return 'gesnduheit';
    }

    private function assertCategoryResultTabNotPresent()
    {
        $this->assertDomElementCount(self::SEARCH_RESULT_XPATH, 0, 'Category result tab should not be present');
    }

    private function assertCategoryResultTabPresent()
    {
        $this->assertDomElementPresent(self::SEARCH_RESULT_XPATH, 'Category result tab should be present');
    }

    private function assertCategoryInResult($categoryFixture, $message)
    {
        $this->assertDomElementContains(
            self::SEARCH_RESULT_XPATH,
            $categoryFixture->getUrlKey(),
            "Failed asserting that cetagory result tab contains category URL key\n\n" .
            $message
        );
    }

    private function assertCategoryNotInResult($categoryFixture, $message)
    {
        $this->assertDomElementNotContains(
            self::SEARCH_RESULT_XPATH,
            $categoryFixture->getUrlKey(),
            "Failed asserting that cetagory result tab does not contain category URL key\n\n" .
            $message
        );
    }
}