<?php
namespace PersonalizeAI\SocialMedia\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use PersonalizeAI\SocialMedia\Block\FacebookOAuth;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

class FacebookOAuthTest extends TestCase
{
    /**
     * @var FacebookOAuth
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        // Create mocks for the dependencies
        $this->contextMock = $this->createMock(Context::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        // Configure the context mock to return the scope config mock
        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        // Instantiate the FacebookOAuth block with mocked dependencies
        $this->block = new FacebookOAuth(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testIsLoggedIn()
    {
        // Arrange: Simulate that the user is logged in
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        // Act: Call the method under test
        $result = $this->block->isLoggedIn();

        // Assert: Check that the method returns true when logged in
        $this->assertTrue($result);
    }

    public function testGetWelcomeLoggedIn()
    {
        // Arrange: Simulate that the user is logged in
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        // Arrange: Set up a mock customer with a name
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn(new \Magento\Framework\DataObject(['name' => 'Test User']));

        // Act: Call the method under test
        $result = $this->block->getWelcome();

        // Assert: Check that the welcome message includes the user's name
        $this->assertEquals('Welcome, Test User!', $result);
    }

    public function testGetWelcomeGuest()
    {
        // Arrange: Simulate that the user is not logged in
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        // Act: Call the method under test
        $result = $this->block->getWelcome();

        // Assert: Check that the welcome message is for a guest
        $this->assertEquals('Welcome, Guest!', $result);
    }

    public function testGetAppId()
    {
        // Arrange: Set up the expected app ID in the configuration
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('facebook_oauth/general/app_id')
            ->willReturn('123456789');

        // Act: Call the method under test
        $result = $this->block->getAppId();

        // Assert: Check that the correct app ID is returned
        $this->assertEquals('123456789', $result);
    }
}
