<?php
namespace PersonalizeAI\SocialMedia\Test\Unit\Block;

use PersonalizeAI\SocialMedia\Block\FacebookOAuth;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GetFacebookIsLinkedU1Test extends TestCase
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
            ->addMethods(['getFacebookName'])
            ->getMock();
    
        // Instantiate the FacebookOAuth block with mocked dependencies
        $this->facebookOAuth = new FacebookOAuth(
            $this->contextMock,
            $this->customerSessionMock
        );
    }
    
    public function testGetFacebookIsLinkedWhenLoggedInAndLinked()
    {
        // Arrange: Simulate that the user is logged in and Facebook is linked
        $this->customerSessionMock->method('isLoggedIn')->willReturn(true);
        $this->customerSessionMock->method('getFacebookName')->willReturn('Test User');

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookIsLinked();

        // Assert: Check that the method returns true when logged in and linked
        $this->assertTrue($result);
    }

    public function testGetFacebookIsLinkedWhenLoggedInButNotLinked()
    {
        // Arrange: Simulate that the user is logged in but Facebook is not linked
        $this->customerSessionMock->method('isLoggedIn')->willReturn(true);
        $this->customerSessionMock->method('getFacebookName')->willReturn(null);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookIsLinked();

        // Assert: Check that the method returns false when logged in but not linked
        $this->assertFalse($result);
    }

    public function testGetFacebookIsLinkedWhenNotLoggedIn()
    {
        // Arrange: Simulate that the user is not logged in
        $this->customerSessionMock->method('isLoggedIn')->willReturn(false);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookIsLinked();

        // Assert: Check that the method returns false when not logged in
        $this->assertFalse($result);
    }
}
