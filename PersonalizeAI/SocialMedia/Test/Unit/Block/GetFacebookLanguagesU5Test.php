<?php
namespace PersonalizeAI\SocialMedia\Test\Unit\Block;

use PersonalizeAI\SocialMedia\Block\FacebookOAuth;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GetFacebookLanguagesU5Test extends TestCase
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
            ->addMethods(['getFacebookLanguages'])
            ->getMock();

        // Instantiate the FacebookOAuth block with mocked dependencies
        $this->facebookOAuth = new FacebookOAuth(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testGetFacebookLanguagesReturnsSessionValue()
    {
        // Arrange: Simulate that languages are set in the session
        $expectedLanguages = ['English', 'Spanish'];
        $this->customerSessionMock->method('getFacebookLanguages')->willReturn($expectedLanguages);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookLanguages();

        // Assert: Check that the correct languages are returned
        $this->assertEquals($expectedLanguages, $result);
    }

    public function testGetFacebookLanguagesReturnsNullWhenNoLanguagesSet()
    {
        // Arrange: Simulate that no languages are set in the session
        $this->customerSessionMock->method('getFacebookLanguages')->willReturn(null);

        // Act: Call the method under test
        $result = $this->facebookOAuth->getFacebookLanguages();

        // Assert: Check that null is returned when no languages are set
        $this->assertNull($result);
    }
}
