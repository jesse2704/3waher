<?php
namespace PersonalizeAI\SocialMedia\Test\Unit\Block;

use PersonalizeAI\SocialMedia\Block\FacebookOAuth;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GetModuleSwitchU7Test extends TestCase
{
    /**
     * @var FacebookOAuth
     */
    private $facebookOAuth;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

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
        $this->facebookOAuth = new FacebookOAuth(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testGetModuleStatusEnabled()
    {
        // Arrange: Set up the configuration to return 'enabled'
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('facebook_oauth/general/enable')
            ->willReturn('1');

        // Act: Call the method under test
        $result = $this->facebookOAuth->getModuleStatus();

        // Assert: Check that the module status is enabled
        $this->assertEquals('1', $result);
    }

    public function testGetModuleStatusDisabled()
    {
        // Arrange: Set up the configuration to return 'disabled'
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('facebook_oauth/general/enable')
            ->willReturn('0');

        // Act: Call the method under test
        $result = $this->facebookOAuth->getModuleStatus();

        // Assert: Check that the module status is disabled
        $this->assertEquals('0', $result);
    }

    public function testGetModuleStatusReturnsNull()
    {
        // Arrange: Set up the configuration to return null
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('facebook_oauth/general/enable')
            ->willReturn(null);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getModuleStatus();

        // Assert: Check that the method returns null when no value is set
        $this->assertNull($result);
    }
}
