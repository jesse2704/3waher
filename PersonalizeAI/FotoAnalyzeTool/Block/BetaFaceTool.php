<?php
namespace PersonalizeAI\FotoAnalyzeTool\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Personalizer\Recommendation\Block\Product\RecommondationService;

class BetaFaceTool extends Template
{
    protected $personalizeRecommondationAI;
    protected $customerSession;

    public function __construct(
        Context $context,
        Session $customerSession,
        RecommondationService $personalizeRecommondationAI,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->personalizeRecommondationAI = $personalizeRecommondationAI;
    }

    /**
     * Retrieve the status of the module from configuration settings.
     *
     * @return mixed Module status.
     */
    public function getModuleStatus()
    {
        return $this->_scopeConfig->getValue('photo_analyze/general/enable'); // Get status from configuration
    }

    /**
     * Check if the user is logged in.
     *
     * @return bool True if logged in, false otherwise.
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Check if Facebook is linked to the user account.
     *
     * @return bool True if Facebook is linked, false otherwise.
     */
    public function getFacebookIsLinked()
    {
        return $this->isLoggedIn() && !empty($this->customerSession->getFacebookName());
    }

    /**
     * Get the Facebook profile picture URL of the logged-in user.
     *
     * @return string|false The profile picture URL or false if not available.
     */
    public function getFacebookProfilePicUrl()
    {
        if ($this->customerSession->isLoggedIn()) {
            $profilePicUrl = $this->customerSession->getFacebookProfilePicUrl();
            if ($profilePicUrl) {
                return $profilePicUrl;
            }
        }
        return false;
    }

    /**
     * Check if self-upload feature is enabled in configuration.
     *
     * @return bool True if self-upload is enabled, false otherwise.
     */
    public function isSelfUploadEnabled()
    {
        return $this->_scopeConfig->getValue(
            'photo_analyze/general/enable_self_upload',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Betaface tags from the customer session if the user is logged in.
     *
     * @return array|false Array of Betaface tags or false if not logged in.
     */
    public function getBetafaceTags()
    {
        if ($this->customerSession->isLoggedIn() && $this->customerSession->getBetafaceTags()) {
            return $this->customerSession->getBetafaceTags();
        }
        return false; // Return false if not logged in
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

        // Define tag names that are relevant for appearance attributes
        $relevantTagNames = [
            'beard',
            'mustache',
            'goatee',
            'bald',
            'black hair',
            'grey hair',
            'brown hair',
            'heavy makeup',
            'receding hairline',
            'straight hair',
            'hair beard',
            'hair length',
            'wavy hair',
            'color hair',
        ];

        // Set a confidence threshold (e.g., 0.7 for 70%)
        $confidenceThreshold = 0.7;

        // Initialize an array to hold tags
        $relevantTags = [];

        foreach ($tags as $tag) {
            // Check if the tag name is in the list of tag names
            // and if the confidence level meets the threshold
            if (in_array($tag['name'], $relevantTagNames) && $tag['confidence'] >= $confidenceThreshold) {
                // Add relevant tag to the result array without confidence level
                $relevantTags[] = [
                    'name' => $tag['name'],
                    'value' => $tag['value'],
                    //'confidence' => $tag['confidence']
                ];
            }
        }

        return $relevantTags; // Return filtered tags
    }

    /**
     * Get the loaded product collection based on recommendations.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getLoadedProductCollection()
    {
        if ($this->isModuleEnabled()) {
            $recommendations = $this->personalizeRecommondationAI->generateRecommendationForYou(getRelevantTagsForYouBanner());
            
            if (empty($recommendations)) {
                return $this->productCollectionFactory->create();
            }

            $productIds = array_column($recommendations, 'id');

            $collection = $this->productCollectionFactory->create();
            $collection->addIdFilter($productIds)
                ->addAttributeToSelect('*');

            $orderByField = new \Zend_Db_Expr("FIELD(e.entity_id, " . implode(',', $productIds) . ")");
            $collection->getSelect()->order($orderByField);

            return $collection;
        } else {
            // Default product collection for widget
            return parent::getLoadedProductCollection();
        }
    }
}
