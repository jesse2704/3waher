<?php
namespace PersonalizeAI\SocialMedia\Plugin\Checkout\Model;

use Magento\Customer\Model\Session;

class LayoutProcessor
{
    protected $customerSession;

    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout)
    {
        // Get userdata from customer session
        $firstname = $this->customerSession->getFacebookFirstName() ?: '';
        $lastname = $this->customerSession->getFacebookLastName() ?: '';
        $telephone = '';
        $postcode = '';
        $city = $this->customerSession->getFacebookLocation() ?: '';
        $country = $this->customerSession->getFacebookCountry() ?: '';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['firstname']['value'] = $firstname;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['lastname']['value'] = $lastname;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['value'] = $telephone;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['value'] = $postcode;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['value'] = $city;
        // $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        //     ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id'] = [
        //         'component' => 'Magento_Checkout/js/view/shipping-address/default',
        //         'template' => 'Magento_Checkout/shipping-address/country',
        //         'value' => $country,
        //         'sortOrder' => 20,
        //         'visible' => true,
        //         'required' => true,
        //         'validation' => [
        //             'required-entry' => true
        //         ]
        //     ];

        return $jsLayout;
    }
}
