<?php

namespace PersonalizeAI\FotoAnalyzeTool\Service;

use Magento\Customer\Model\Session;

class FacebookProfilePicture
{
    protected $customerSession;

    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Get the URL of the Facebook profile picture if logged in.
     *
     * @return string|false
     */
    public function getFacebookProfilePicUrl()
    {
        if ($this->customerSession->isLoggedIn() && $this->customerSession->getFacebookProfilePicUrl()) {
            return $this->customerSession->getFacebookProfilePicUrl();
        }
        return false; // Return false if not logged in or no picture URL available
    }
}
