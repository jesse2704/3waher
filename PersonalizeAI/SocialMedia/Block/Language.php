<?php

namespace PersonalizeAI\SocialMedia\Block;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;

class Language extends Template
{
    protected $customerSession;
    protected $switcher;

    public function __construct(
        Session $customerSession,
        StoreManagerInterface $Switcher,
        Context $context,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->switcher = $Switcher;
        parent::__construct($context, $data);
    }

    // Check if the user is logged in
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getFacebookIsLinked()
    {
        return $this->isLoggedIn() && !empty($this->customerSession->getFacebookName());
    }

    public function getFacebookLanguages()
    {
        return $this->customerSession->getFacebookLanguages();
    }

    public function getCurrentLanguage()
    {
        return $this->switcher->getStore()->getConfig('general/locale/code');
    }

    public function changeStoreView($store)
    {
        $baseUrl = $this->switcher->getStore('NL')->getBaseUrl();
        $storeCode = $store;
        
        return $baseUrl . 'stores/store/redirect/___store/' . $storeCode . '/___from_store/default';
    }
}