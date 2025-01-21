<?php 
namespace PersonalizeAI\SocialMedia\Controller\Oauth;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class SaveUserData
 *
 * This controller handles the saving of user data from Facebook into the Magento customer session.
 */
class SaveUserData extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * SaveUserData constructor.
     *
     * @param Context $context The action context.
     * @param CustomerSession $customerSession The customer session model.
     * @param JsonFactory $resultJsonFactory The factory for creating JSON results.
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        // Initialize the customer session and JSON result factory
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute method to save user data and return a JSON response.
     *
     * This method collects user data from the request, validates it,
     * and saves it to the customer session. It returns a JSON response
     * indicating success or failure.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Create a JSON result object to hold the response data
        $resultJson = $this->resultJsonFactory->create();
        
        // Collect user data from the request parameters
        $userData = $this->collectUserData();   

        // Validate the collected user data before processing
        if ($this->validateUserData($userData)) {
            try {
                // Save user data to the customer session using appropriate methods
                $this->customerSession->setFacebookId($userData['id']);
                $this->customerSession->setFacebookFirstName($userData['firstname']);
                $this->customerSession->setFacebookLastName($userData['lastname']);
                $this->customerSession->setFacebookName($userData['name']);
                $this->customerSession->setFacebookEmail($userData['email']);
                $this->customerSession->setFacebookProfilePicUrl($userData['profile_pic_url']);
                $this->customerSession->setFacebookGender($userData['gender']);
                $this->customerSession->setFacebookBirthday($userData['birthday']);
                $this->customerSession->setFacebookHometown($userData['hometown']);
                $this->customerSession->setFacebookLocation($userData['location']);
                $this->customerSession->setFacebookCountry($userData['country']);
                $this->customerSession->setFacebookFriends($userData['friends']);
                $this->customerSession->setFacebookPosts($userData['posts']);
                $this->customerSession->setFacebookLikes($userData['likes']);
                $this->customerSession->setFacebookLanguages($userData['languages']);
                $this->customerSession->setFacebookFavoriteTeams($userData['favorite_teams']);


                // $this->customerSession->setFacebookPaymentSubscriptions($userData['payment_subscriptions']);

                // Return a success message if data is saved successfully
                return $resultJson->setData([
                    'success' => true,
                    'message' => 'User data saved successfully'
                ]);
            } catch (\Exception $e) {
                // Handle any exceptions that occur during saving and return an error message
                return $resultJson->setData([
                    'success' => false,
                    'message' => 'Error saving user data: ' . $e->getMessage()
                ]);
            }
        }

        // Return an error message if validation fails
        return $resultJson->setData([
            'success' => false,
            'message' => 'Invalid or incomplete user data provided'
        ]);
    }

    /**
     * Collect user data from the request parameters.
     *
     * This method retrieves various parameters from the request and organizes them 
     * into an associative array for further processing.
     *
     * @return array Collected user data.
     */
    private function collectUserData()
    {
        return [
            'id' => $this->getRequest()->getParam('id'), // Facebook ID of the user
            'firstname' => $this->getRequest()->getParam('firstname'), // User's first name
            'lastname' => $this->getRequest()->getParam('lastname'), // User's last name
            'name' => $this->getRequest()->getParam('name'), // Full name of the user
            'email' => $this->getRequest()->getParam('email'), // User's email address
            'profile_pic_url' => $this->getRequest()->getParam('profile_pic_url'), // Profile picture URL
            'gender' => $this->getRequest()->getParam('gender'), // User's gender
            'birthday' => $this->getRequest()->getParam('birthday'), // User's birthday
            'hometown' => $this->getRequest()->getParam('hometown'), // User's hometown information
            'location' => $this->getRequest()->getParam('location'), // Current location of the user
            'country' => $this->getRequest()->getParam('country'), // User's country information
            'friends' => $this->getRequest()->getParam('friends'), // List of user's friends (if applicable)
            'posts' => $this->getRequest()->getParam('posts'), // User's posts 
            'likes' => $this->getRequest()->getParam('likes'), // User's likes
            'languages' => $this->getRequest()->getParam('languages'), // Languages preference from the user
            'favorite_teams' => $this->getRequest()->getParam('favorite_teams') // User's favorite teams 
        ];
    }

    /**
     * Validate the collected user data.
     *
     * This method checks that essential fields are present and not empty.
     *
     * @param array $userData User data to validate.
     * @return bool True if valid, false otherwise.
     */
    private function validateUserData($userData)
    {
        return !empty($userData['id']) && !empty($userData['name']) && !empty($userData['email']); 
    }
}
