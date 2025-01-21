<?php
namespace PersonalizeAI\SocialMedia\Controller\Oauth;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\JsonFactory;

class UnlinkFacebook extends Action
{
    protected $customerSession;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            // Set all Facebook-related session data to null
            $this->customerSession->setFacebookId(null);
            $this->customerSession->setFacebookFirstName(null);
            $this->customerSession->setFacebookLastName(null);
            $this->customerSession->setFacebookName(null);
            $this->customerSession->setFacebookEmail(null);
            $this->customerSession->setFacebookProfilePicUrl(null);
            $this->customerSession->setFacebookGender(null);
            $this->customerSession->setFacebookBirthday(null);
            $this->customerSession->setFacebookHometown(null);
            $this->customerSession->setFacebookLocation(null);
            $this->customerSession->setFacebookCountry(null);
            $this->customerSession->setFacebookFriends(null);
            $this->customerSession->setFacebookPosts(null);
            $this->customerSession->setFacebookLikes(null);
            $this->customerSession->setFacebookLanguages(null);
            $this->customerSession->setFacebookFavoriteTeams(null);

            return $resultJson->setData([
                'success' => true,
                'message' => 'Facebook account unlinked successfully'
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => 'Error unlinking Facebook account: ' . $e->getMessage()
            ]);
        }
    }
}
