<?php
namespace PersonalizeAI\SocialMedia\Controller\Oauth;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class ShowSessionData
 *
 * This controller handles the retrieval of Facebook session data for the authenticated user.
 */
class ShowSessionData extends Action
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
     * ShowSessionData constructor.
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
     * Execute method to retrieve session data and return as JSON response.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Retrieve Facebook data from the customer session
        $facebookId = $this->customerSession->getFacebookId();
        $facebookName = $this->customerSession->getFacebookName();
        $facebookFirstName = $this->customerSession->getFacebookFirstName();
        $facebookLastName = $this->customerSession->getFacebookLastName();
        $facebookEmail = $this->customerSession->getFacebookEmail();
        $facebookProfilePicUrl = $this->customerSession->getFacebookProfilePicUrl();
        $facebookGender = $this->customerSession->getFacebookGender();
        $facebookBirthday = $this->customerSession->getFacebookBirthday();
        $facebookHometown = $this->customerSession->getFacebookHometown();
        $facebookLocation = $this->customerSession->getFacebookLocation();
        $facebookCountry = $this->customerSession->getFacebookCountry();
        $facebookAgeRange = $this->customerSession->getFacebookAgeRange();

        // Retrieve Facebook friends data from the customer session
        $facebookFriends = $this->decodeJsonData($this->customerSession->getFacebookFriends());

        // Retrieve other Facebook data with JSON decoding where applicable
        $facebookPosts = $this->decodeJsonData($this->customerSession->getFacebookPosts());
        $facebookLikes = $this->decodeJsonData($this->customerSession->getFacebookLikes());
        $facebookLanguages = $this->decodeJsonData($this->customerSession->getFacebookLanguages());
        $facebookFavoriteTeams = $this->decodeJsonData($this->customerSession->getFacebookFavoriteTeams());
        
        // Prepare data array for JSON response
        // Using null coalescing operator to ensure we always return an array or empty array if not set.
        $data = [
            'facebook_id' => $facebookId,
            'first_name' => $facebookFirstName,
            'last_name' => $facebookLastName,
            'name' => $facebookName,
            'email' => $facebookEmail,
            'profile_pic_url' => $facebookProfilePicUrl,
            'gender' => $facebookGender,
            'birthday' => $facebookBirthday,
            'hometown' => $facebookHometown,
            'location' => $facebookLocation,
            'country' => $facebookCountry,
            'friends' => (array)$facebookFriends ?? [],
            'posts' => (array)$facebookPosts ?? [],
            'likes' => (array)$facebookLikes ?? [],
            'languages' => (array)$facebookLanguages ?? [],
            'favorite_teams' => (array)$facebookFavoriteTeams ?? [],
            'age_range' => (array)$this->customerSession->getFacebookAgeRange() ?? [],
            // 'payment_subscriptions' => (array)$this->customerSession->getFacebookPaymentSubscriptions() ?? []
        ];

        /** @var \Magento\Framework\Controller\Result\Json */
        // Create JSON result object and return data as a JSON response
        return $this->resultJsonFactory->create()->setData($data);
    }

    /**
     * Decode JSON data safely.
     *
     * @param mixed|null $data The data to decode.
     * @return array Decoded array or empty array on failure.
     */
    private function decodeJsonData($data)
    {
        if (is_string($data)) {
            // Attempt to decode JSON string
            return json_decode($data, true) ?? [];
        } elseif (is_array($data)) {
            // If already an array, return it directly
            return $data;
        }
        
        // Default to an empty array if neither string nor array
        return [];
    }
}
