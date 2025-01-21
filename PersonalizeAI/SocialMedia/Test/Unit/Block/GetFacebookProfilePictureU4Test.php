<?php
namespace PersonalizeAI\SocialMedia\Test\Unit\Block;

use PersonalizeAI\SocialMedia\Block\FacebookOAuth;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GetFacebookProfilePictureU4Test extends TestCase
{
    /**
     * @var FacebookOAuth
     */
    private $facebookOAuth;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    protected function setUp(): void
    {
        // Create a mock for the Template context
        $this->contextMock = $this->createMock(Context::class);
        
        // Create a mock for the CustomerSession with specific methods
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLoggedIn'])
            ->addMethods(['getFacebookProfilePicUrl'])
            ->getMock();

        // Instantiate the FacebookOAuth block with mocked dependencies
        $this->facebookOAuth = new FacebookOAuth(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testGetFacebookProfilePicUrlWhenLoggedIn()
    {
        // Arrange: Simulate that the user is logged in and has a profile picture URL
        $expectedProfilePicUrl = 'http://example.com/profile.jpg';
        
        // Mock the isLoggedIn method to return true
        $this->customerSessionMock->method('isLoggedIn')->willReturn(true);
        
        // Mock the getFacebookProfilePicUrl method to return the expected URL
        $this->customerSessionMock->method('getFacebookProfilePicUrl')->willReturn($expectedProfilePicUrl);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookProfilePicUrl();

        // Assert: Check that the correct URL is returned
        $this->assertEquals($expectedProfilePicUrl, $result);
    }

    public function testGetFacebookProfilePicUrlWhenNotLoggedIn()
    {
    // Arrange: Simulate that the user is not logged in
        $this->customerSessionMock->method('isLoggedIn')->willReturn(false);

    // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookProfilePicUrl();

    // Assert: Check that false is returned when not logged in
        $this->assertFalse($result);
    }

    public function testGetFacebookProfilePicUrlWhenNoPictureAvailable()
    {
        // Arrange: Simulate that the user is logged in but no picture URL is available
        $this->customerSessionMock->method('isLoggedIn')->willReturn(true);
        $this->customerSessionMock->method('getFacebookProfilePicUrl')->willReturn(null);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookProfilePicUrl();

        // Assert: Check that null is returned when no picture URL is available
        $this->assertFalse  ($result);
    }
}
