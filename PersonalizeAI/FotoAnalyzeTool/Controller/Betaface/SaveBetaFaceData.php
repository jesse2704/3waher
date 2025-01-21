<?php
namespace PersonalizeAI\FotoAnalyzeTool\Controller\Betaface;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\JsonFactory;
use PersonalizeAI\FotoAnalyzeTool\Model\TagProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class SaveBetaFaceData
 *
 * This controller handles the saving of Betaface data, specifically facial recognition tags,
 * associated with a logged-in user.
 */
class SaveBetaFaceData extends Action
{
    protected $customerSession;
    protected $resultJsonFactory;
    protected $tagProcessor; 
    protected $logger; 

    /**
     * SaveBetaFaceData constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param JsonFactory $resultJsonFactory
     * @param TagProcessor $tagProcessor 
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        TagProcessor $tagProcessor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagProcessor = $tagProcessor; 
        $this->logger = $logger;
    }

    /**
     * Execute the action to save Betaface data.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            // Get raw content from the request body
            $content = $this->getRequest()->getContent();
            // Decode the JSON content into an associative array
            $data = json_decode($content, true);

            // Validate that 'tags' are present and are in an array format
            if (!isset($data['tags']) || !is_array($data['tags'])) {
                throw new \InvalidArgumentException('Invalid or missing tags data');
            }

            // Process the tags collected from the request
            if (!$this->customerSession->isLoggedIn()) {
                throw new \RuntimeException('User not logged in.');
            }

            // Use the tag processor to handle tag processing
            $processedData = $this->tagProcessor->processTags($data['tags']);
            // Store processed tags in the customer session
            $this->customerSession->setBetafaceTags($processedData['tags']);

            return $resultJson->setData([
                'success' => true,
                'message' => 'Tags saved successfully.',
                'data' => $processedData,
            ]);
        } catch (\Exception $e) {
            // Log any errors that occur during processing
            $this->logger->error($e->getMessage());
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
