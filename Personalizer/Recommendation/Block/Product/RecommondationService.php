<?php
namespace Personalizer\Recommendation\Block\Product;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use PersonalizeAI\SmartRecommend\Model\ExplicitDataService;

class RecommondationService
{
    protected $curl;
    protected $cookieManager;
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $visibility;
    protected $storeManager;
    protected $explicitDataService;

    public function __construct(
        Curl $curl,
        CookieManagerInterface $cookieManager,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Visibility $visibility,
        StoreManagerInterface $storeManager,
        ExplicitDataService $explicitDataService
    ) {
        $this->curl = $curl;
        $this->cookieManager = $cookieManager;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->visibility = $visibility;
        $this->storeManager = $storeManager;
        $this->explicitDataService = $explicitDataService;
    }

    public function generateRecommendation()
    {
        $products = $this->explicitDataService->getAllProducts();
        //print_r($products);
        
        $productList = $this->formatProductList($products);
        
        $personalizeAccepted = $this->cookieManager->getCookie('personalize_accepted') ?? '';
        $extraPersonalization = $this->explicitDataService->getPrompt();
    
        $query = $this->buildAIQuery($personalizeAccepted, $productList, $extraPersonalization);
    
        $result = $this->makeAIRequest($query);
        // print_r($result);

        
        return $this->processAIResponse($result, $products);
    }

    public function generateRecommendationForYou($tags)
    {
        $products = $this->explicitDataService->getAllProducts();
        $productList = $this->formatProductList($products);
        $query = $this->buildAIQueryForYou($tags, $productList);
        $result = $this->makeAIRequest($query);

        return $this->processAIResponse($result, $products);
    }
    
    private function formatProductList($products)
    {
        return array_map(function ($product) {
            return "{" . $product['id'] . ", " . $product['name'] . "} (Price: " . $product['price'] . ", Color: " . ($product['color'] ?? 'N/A') . ")";
        }, $products);
    }

    private function buildAIQueryForYou($tags, $productList)
    {
        // Ensure $tags is a string representation if it's an array
        if (is_array($tags)) {
            $tags = implode(', ', array_column($tags, 'name')); // Convert tags to a comma-separated string
        }

        $productListString = implode(', ', $productList);
        $query = "Consider a customer with the following appearance attributes: $tags.\n\n";
        $query .= "Here is the list of available products:\n$productListString\n\n";
        $query .= "Please identify all products that are relevant to this customer based on their interests and attributes.\n";
        $query .= "Return an ordered list (string) in format (id, name) NO TEKST";

        return preg_replace('/(\r\n|\n|\r)/', ' ', $query);
    }

    
    private function buildAIQuery($interests, $productList, $extraPersonalization)
    {
        $productListString = implode(', ', $productList);
        $query = "Consider a customer with the following appearance attributes: $interests.\n\n";
        $query .= "Here is the list of available products:\n$productListString\n\n";
        $query .= "Please identify all products that are relevant to this customer based on their interests and attributes.";
        $query .= "Return an ordered list (string) in format (id, name) NO TEKST";
        
        return preg_replace('/(\r\n|\n|\r)/', ' ', $query);
    }
    
    private function makeAIRequest($query)
    {
        $url = 'https://ai-server.regem.in/api/index.php';
        $this->curl->post($url, ['input' => $query]);
        $result = $this->curl->getBody();
    
        if (empty($result) || strpos($result, "Try Again! or May be Server is Down!") !== false) {
            return '';
        }
    
        return str_replace(["regem", "Regem"], ["openai", "Openai"], $result);
    }
    
    private function processAIResponse($result, $originalProducts)
    {
        if (empty($result)) {
            return [];
        }
    
        $recommendedProducts = explode(', ', $result);
        $finalRecommendations = [];
    
        foreach ($recommendedProducts as $productName) {
            foreach ($originalProducts as $product) {
                if (strpos($productName, $product['name']) !== false) {
                    $finalRecommendations[] = $product;
                    break;
                }
            }
        }
    
        return $finalRecommendations;
    }
}
