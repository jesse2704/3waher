<?php
namespace PersonalizeAI\FotoAnalyzeTool\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Personalizer\Recommendation\Block\Product\RecommondationService;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Class ForYouRecommendations
 *
 * This class handles personalized recommendations based on user tags and preferences.
 */
class ForYouRecommendations extends ListProduct
{
    /**
     * @var RecommondationService
     */
    protected $personalizeRecommondationAI;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * ForYouRecommendations constructor.
     *
     * @param Context $context The template context.
     * @param PostHelper $postDataHelper Helper for post data.
     * @param Resolver $layerResolver Layer resolver for product layers.
     * @param CategoryRepositoryInterface $categoryRepository Category repository interface.
     * @param UrlHelper $urlHelper URL helper for generating URLs.
     * @param RecommondationService $personalizeRecommondationAI Service for generating recommendations.
     * @param CollectionFactory $productCollectionFactory Product collection factory.
     * @param Session $customerSession The customer session model.
     * @param array $data Additional data for the block.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        UrlHelper $urlHelper,
        RecommondationService $personalizeRecommondationAI,
        CollectionFactory $productCollectionFactory,
        Session $customerSession,
        array $data = []
    ) {
        // Initialize dependencies
        $this->personalizeRecommondationAI = $personalizeRecommondationAI;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customerSession = $customerSession;

        // Call parent constructor to initialize the template context and required dependencies
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Retrieve Betaface tags from the customer session.
     *
     * @return array Array of Betaface tags or an empty array if not logged in.
     */
    public function getBetafaceTags()
    {
        if ($this->customerSession->isLoggedIn() && $this->customerSession->getBetafaceTags()) {
            return $this->customerSession->getBetafaceTags();
        }
        return []; // Return an empty array if not logged in or no tags found
    }

    /**
     * Get relevant tags for personalized recommendations based on confidence levels.
     *
     * This method filters the full array of tags and returns only those that are relevant
     * for making personalized recommendations based on a confidence threshold.
     *
     * @return array Array of relevant tags for recommendations.
     */
    public function getRelevantTagsForYouBanner()
    {
        // Retrieve Betaface tags from the customer session
        $tags = $this->getBetafaceTags();

        // Check if tags are valid
        if (empty($tags)) {
            return []; // Return empty array if no tags are found
        }

        // Define relevant tag names and confidence threshold
        $relevantTagNames = [
            'beard', 'mustache', 'goatee', 'bald', 'black hair',
            'grey hair', 'brown hair', 'heavy makeup', 'receding hairline',
            'straight hair', 'hair beard', 'hair length', 'wavy hair',
            'color hair'
        ];
        
        $confidenceThreshold = 0.7; // Set a confidence threshold (e.g., 70%)

        // Initialize an array to hold relevant tags
        $relevantTags = [];

        foreach ($tags as $tag) {
            // Check if the tag name is in the list of relevant tag names and meets confidence level
            if (in_array($tag['name'], $relevantTagNames) && $tag['confidence'] >= $confidenceThreshold) {
                // Add relevant tag to the result array without confidence level
                $relevantTags[] = [
                    'name' => $tag['name'],
                    'value' => $tag['value'],
                ];
            }
        }

        return $relevantTags; // Return filtered tags
    }

     /**
      * Format the price for display.
      *
      * @param float $price The price to format.
      * @param bool $includeContainer Whether to include currency container.
      * @return string Formatted price.
      */
    public function formatPrice($price, $includeContainer = true)
    {
        if ($includeContainer) {
            return $this->_storeManager->getStore()->getBaseCurrency()->format($price);
        } else {
            return number_format($price, 2, '.', '');
        }
    }

    /**
     * Get the loaded product collection based on recommendations.
     *
     * This method generates recommendations based on user tags and retrieves a product collection
     * filtered by those recommendations.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|array Product collection or empty array if no products found.
     */
    public function getLoadedProductCollection()
    {
        if ($this->getModuleStatus()) {
            // Get relevant tags for recommendations
            $allTags = $this->getRelevantTagsForYouBanner();
            // Generate recommendations based on these tags
            $recommendations = $this->personalizeRecommondationAI->generateRecommendationForYou($allTags);
            
            // Debugging: Log recommendations (remove in production)
            // error_log(print_r($recommendations, true));

            if (empty($recommendations)) {
                return parent::getLoadedProductCollection(); // Fallback to default collection if no recommendations found
            }

            // Extract product IDs from recommendations
            $productIds = array_column($recommendations, 'id');

            // Create product collection and filter by IDs
            /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
            $collection = $this->productCollectionFactory->create();
            $collection->addIdFilter($productIds)
                ->addAttributeToSelect('*');

            // Order collection by specified field using MySQL FIELD() function for custom ordering
            if (!empty($productIds)) {
                $orderByField = new \Zend_Db_Expr("FIELD(e.entity_id, " . implode(',', array_map('intval', $productIds)) . ")");
                $collection->getSelect()->order($orderByField);
            }

            return $collection; // Return filtered product collection
        } else {
            return parent::getLoadedProductCollection(); // Fallback to parent method if module is disabled
        }
    }

    /**
     * Check if the module is enabled.
     *
     * @return bool True if enabled, false otherwise.
     */
    private function getModuleStatus()
    {
        return (bool)$this->_scopeConfig->getValue('photo_analyze/general/enable'); // Get status from configuration settings
    }
}
