<?php
namespace PersonalizeAI\FotoAnalyzeTool\Test\Unit\Controller\Betaface;

use PHPUnit\Framework\TestCase;
use PersonalizeAI\FotoAnalyzeTool\Controller\Betaface\SaveBetaFaceData;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use PersonalizeAI\FotoAnalyzeTool\Model\TagProcessor;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

class SaveBetaFaceDataU5Test extends TestCase
{
    // Declaring protected properties for the controller and its dependencies
    protected $controller;
    protected $contextMock;
    protected $customerSessionMock;
    protected $resultJsonFactoryMock;
    protected $tagProcessorMock;
    protected $loggerMock;
    protected $requestMock;
    protected $resultJsonMock;

    // setUp method to initialize mocks and the controller before each test
    protected function setUp(): void
    {
        // Creating mock objects for all dependencies
        $this->contextMock = $this->createMock(Context::class);
        
        // Creating a partial mock for CustomerSession with specific methods
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLoggedIn'])
            ->addMethods(['setBetafaceTags'])
            ->getMock();
        
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->tagProcessorMock = $this->createMock(TagProcessor::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        
        // Creating a mock for the HTTP request
        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        // Configuring the context mock to return the request mock
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        
        // Creating and configuring the JSON result mock
        $this->resultJsonMock = $this->createMock(Json::class);
        $this->resultJsonFactoryMock->method('create')->willReturn($this->resultJsonMock);
    
        // Instantiating the controller with mocked dependencies
        $this->controller = new SaveBetaFaceData(
            $this->contextMock,
            $this->customerSessionMock,
            $this->resultJsonFactoryMock,
            $this->tagProcessorMock,
            $this->loggerMock
        );
    }

    // Test case for executing the controller with valid tags
    public function testExecuteWithValidTags()
    {
        // Preparing test data
        $tags = [['name' => 'tag1', 'value' => 'value1']];
        $jsonContent = json_encode(['tags' => $tags]);
        $processedData = ['tags' => $tags, 'extra' => 'data'];

        // Configuring mocks to simulate the expected behavior
        $this->requestMock->method('getContent')->willReturn($jsonContent);
        $this->customerSessionMock->method('isLoggedIn')->willReturn(true);
        $this->tagProcessorMock->method('processTags')->with($tags)->willReturn($processedData);

        // Capturing the data being saved
        $savedTags = null;
        $this->customerSessionMock->expects($this->once())
            ->method('setBetafaceTags')
            ->with($this->callback(function ($arg) use (&$savedTags) {
                $savedTags = $arg;
                return true;
            }));

        // Expecting the result to be set with specific data
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([
                'success' => true,
                'message' => 'Tags saved successfully.',
                'data' => $processedData,
            ])
            ->willReturnSelf();

        // Executing the controller
        $result = $this->controller->execute();

        // Asserting that the result is as expected
        $this->assertSame($this->resultJsonMock, $result);
    
        // Asserting that the saved tags match the processed tags
        $this->assertEquals($processedData['tags'], $savedTags);
    }

    // Test case for executing the controller with invalid tags
    public function testExecuteWithInvalidTags()
    {
        // Preparing test data with invalid tags
        $jsonContent = json_encode(['tags' => null]);

        // Configuring mocks to simulate invalid tag scenario
        $this->requestMock->method('getContent')->willReturn($jsonContent);

        // Expecting the result to indicate failure due to invalid tags
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'message' => 'Invalid or missing tags data',
            ])
            ->willReturnSelf();

        // Expecting an error to be logged
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Invalid or missing tags data');

        // Executing the controller
        $result = $this->controller->execute();

        // Asserting that the result is as expected
        $this->assertSame($this->resultJsonMock, $result);
    }

    // Test case for executing the controller when the user is not logged in
    public function testExecuteWithUserNotLoggedIn()
    {
        // Preparing test data
        $tags = [['name' => 'tag1', 'value' => 'value1']];
        $jsonContent = json_encode(['tags' => $tags]);

        // Configuring mocks to simulate user not logged in scenario
        $this->requestMock->method('getContent')->willReturn($jsonContent);
        $this->customerSessionMock->method('isLoggedIn')->willReturn(false);

        // Expecting the result to indicate failure due to user not being logged in
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'message' => 'User not logged in.',
            ])
            ->willReturnSelf();

        // Expecting an error to be logged
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('User not logged in.');

        // Executing the controller
        $result = $this->controller->execute();

        // Asserting that the result is as expected
        $this->assertSame($this->resultJsonMock, $result);
    }

    // Test case for executing the controller when tag processing throws an exception
    public function testExecuteWithTagProcessorException()
    {
        // Preparing test data
        $tags = [['name' => 'tag1', 'value' => 'value1']];
        $jsonContent = json_encode(['tags' => $tags]);
        $exceptionMessage = 'Tag processing failed';

        // Configuring mocks to simulate tag processing exception
        $this->requestMock->method('getContent')->willReturn($jsonContent);
        $this->customerSessionMock->method('isLoggedIn')->willReturn(true);
        $this->tagProcessorMock->method('processTags')->willThrowException(new \Exception($exceptionMessage));

        // Expecting the result to indicate failure due to tag processing exception
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'message' => $exceptionMessage,
            ])
            ->willReturnSelf();

        // Expecting the exception message to be logged
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        // Executing the controller
        $result = $this->controller->execute();

        // Asserting that the result is as expected
        $this->assertSame($this->resultJsonMock, $result);
    }
}
