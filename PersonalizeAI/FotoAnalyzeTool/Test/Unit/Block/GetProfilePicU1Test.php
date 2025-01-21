<?php
namespace PersonalizeAI\FotoAnalyzeTool\Test\Unit\Block;

use Magento\Customer\Model\Session as CustomerSession;
use PersonalizeAI\FotoAnalyzeTool\Block\BetaFaceTool;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Element\Template\Context;
use Personalizer\Recommendation\Block\Product\RecommondationService;

class GetProfilePicU1Test extends TestCase
{
    private BetaFaceTool $betaFaceTool;
    private CustomerSession|MockObject $customerSessionMock;
    private Context|MockObject $contextMock;
    private RecommondationService|MockObject $recommendationServiceMock;

    protected function setUp(): void
    {
        // Create mocks for dependencies
        $this->contextMock = $this->createMock(Context::class);

        // Create a mock for the CustomerSession
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['isLoggedIn'])
        ->addMethods(['getFacebookProfilePicUrl'])
        ->getMock();
        
        // Create a mock for the RecommendationService
        $this->recommendationServiceMock = $this->createMock(RecommondationService::class);

        // Instantiate the BetaFaceTool class with the mocked context and other dependencies
        $this->betaFaceTool = new BetaFaceTool(
            $this->contextMock,
            $this->customerSessionMock,
            $this->recommendationServiceMock
        );
    }

    public function testGetFacebookProfilePicUrlWhenLoggedInAndUrlAvailable()
    {
        $expectedUrl = 'https://example.com/profile.jpg';

        // Expect isLoggedIn to be called once and return true
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        // Expect getFacebookProfilePicUrl to be called once and return the expected URL
        $this->customerSessionMock->expects($this->once())
            ->method('getFacebookProfilePicUrl')
            ->willReturn($expectedUrl);

        // Call the method under test and assert the result
        $result = $this->betaFaceTool->getFacebookProfilePicUrl();
        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetFacebookProfilePicUrlWhenNotLoggedIn()
    {
        // Expect isLoggedIn to be called once and return false
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        // Expect getFacebookProfilePicUrl to never be called since user is not logged in
        $this->customerSessionMock->expects($this->never())
            ->method('getFacebookProfilePicUrl');

        // Call the method under test and assert the result
        $result = $this->betaFaceTool->getFacebookProfilePicUrl();
        $this->assertFalse($result);
    }

    public function testGetFacebookProfilePicUrlWhenLoggedInButNoUrlAvailable()
    {
        // Expect isLoggedIn to be called once and return true
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        // Expect getFacebookProfilePicUrl to be called once and return null (or false)
        $this->customerSessionMock->expects($this->once())
            ->method('getFacebookProfilePicUrl')
            ->willReturn(null);

        // Call the method under test and assert the result
        $result = $this->betaFaceTool->getFacebookProfilePicUrl();
        $this->assertFalse($result);
    }
}
