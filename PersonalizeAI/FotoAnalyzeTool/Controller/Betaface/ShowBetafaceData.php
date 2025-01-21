<?php

namespace PersonalizeAI\FotoAnalyzeTool\Controller\Betaface;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class ShowTagsData
 *
 * This controller handles the retrieval of tags for the authenticated user.
 */
class ShowBetafaceData extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * ShowTagsData constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute method to retrieve tags data and return as JSON response.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Retrieve tags data from the customer session
        $tags = $this->customerSession->getBetafaceTags();

        // Prepare data array for JSON response
        // Using null coalescing operator to ensure we always return an array or empty array if not set.
        $data = [
            'tags' => (array)$tags ?? [],
        ];

        /** @var \Magento\Framework\Controller\Result\Json */
        // Create JSON result object and return data as a JSON response
        return $this->resultJsonFactory->create()->setData($data);
    }
}
