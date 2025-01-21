<?php
namespace PersonalizeAI\SocialMedia\Test\Unit\Block;

use PersonalizeAI\SocialMedia\Block\FacebookOAuth;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GetFacebookNameU6Test extends TestCase
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
            ->addMethods(['getFacebookName'])
            ->getMock();

        // Instantiate the FacebookOAuth block with mocked dependencies
        $this->facebookOAuth = new FacebookOAuth(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testGetFacebookNameReturnsFullName()
    {
        // Arrange: Simulate that a full name is set in the session
        $expectedName = 'Test User';
        $this->customerSessionMock->method('getFacebookName')->willReturn($expectedName);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookName();

        // Assert: Check that the correct full name is returned
        $this->assertEquals($expectedName, $result);
        $this->assertIsString($result);
        $this->assertStringContainsString(' ', $result, "Full name should contain a space");
    }

    public function testGetFacebookNameReturnsNullWhenNotSet()
    {
        // Arrange: Simulate that no name is set in the session
        $this->customerSessionMock->method('getFacebookName')->willReturn(null);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookName();

        // Assert: Check that null is returned when no name is set
        $this->assertNull($result);
    }

    public function testGetFacebookNameReturnsOnlyFirstName()
    {
        // Arrange: Simulate that only a first name is set in the session
        $expectedName = 'Test';
        $this->customerSessionMock->method('getFacebookName')->willReturn($expectedName);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookName();

        // Assert: Check that only the first name is returned
        $this->assertEquals($expectedName, $result);
        $this->assertIsString($result);
        $this->assertStringNotContainsString(' ', $result, "Name should not contain a space when only first name is set");
    }

    public function testGetFacebookNameReturnsEmptyString()
    {
        // Arrange: Simulate that an empty string is set as the name in the session
        $this->customerSessionMock->method('getFacebookName')->willReturn('');

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookName();

        // Assert: Check that an empty string is returned
        $this->assertSame('', $result);
        $this->assertIsString($result);
    }
}
