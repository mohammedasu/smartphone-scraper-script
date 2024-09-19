<?php

namespace App;

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Exception;

/**
 * Scraper class to fetch and process smartphone data from the website.
 */
class Scrape
{
    public const BASE_URL = 'https://www.magpiehq.com/developer-challenge/smartphones';
    private const IMAGE_BASE_URL = 'https://www.magpiehq.com/developer-challenge';
    
    public array $products = [];
    private array $productIds = [];

    /**
     * Main method to run the scraping process.
     *
     * @return void
     */
    public function run(): void
    {
        $currentPage = 1;
        $totalPages = $this->getTotalPages(self::BASE_URL);
        
        while ($currentPage <= $totalPages) {
            $url = $this->buildUrl(self::BASE_URL, $currentPage);
            try {
                $document = ScrapeHelper::fetchDocument($url);
                $this->parseDocument($document);
            } catch (Exception $e) {
                echo "Error fetching or parsing page $currentPage: " . $e->getMessage() . PHP_EOL;
            }

            $currentPage++;
        }

        $this->saveToFile('output.json');
    }

    /**
     * Builds the URL for a specific page.
     *
     * @param string $baseUrl
     * @param int $page
     * @return string
     */
    public function buildUrl(string $baseUrl, int $page): string
    {
        return sprintf('%s?page=%d', $baseUrl, $page);
    }

    /**
     * Gets the total number of pages based on the pagination controls.
     *
     * @param string $url
     * @return int
     */
    public function getTotalPages(string $url): int
    {
        $document = ScrapeHelper::fetchDocument($url);
        $crawler = $document->filter('#pages > div');
        $lastPageLink = $crawler->filter('a')->last();
        return (int) $lastPageLink->text();
    }

    /**
     * Parses the HTML document and extracts product information.
     *
     * @param Crawler $document
     * @return void
     */
    public function parseDocument(Crawler $document): void
    {
        $document->filter('.product')->each(function (Crawler $node) {
            $this->extractProductVariants($node);
        });
    }

    /**
     * Extracts product variants from a node.
     *
     * @param Crawler $node
     * @return void
     */
    private function extractProductVariants(Crawler $node): void
    {
        $title = $this->extractTitle($node);
        $price = $this->extractPrice($node);
        $imageUrl = $this->getFullImageUrlFromSource($node->filter('img')->attr('src'));
        $capacityMB = $this->convertCapacityToMB($node->filter('.product-capacity')->text());
        $capacityGB = $node->filter('.product-capacity')->text();
        
        $node->filter('span[data-colour]')->each(function (Crawler $colourNode) use ($node, $title, $price, $imageUrl, $capacityMB, $capacityGB) {
            $colour = $colourNode->attr('data-colour');
            $availabilityText = $this->extractAvailabilityText($node);
            $isAvailable = strpos($availabilityText, 'In Stock') !== false;
            $shippingText = $this->extractShippingText($node);
            $shippingDate = $this->extractShippingDate($node);

            $productId = md5($title . $capacityGB . $colour);
            if (!isset($this->productIds[$productId])) {
                $this->productIds[$productId] = true;

                $productData = [
                    'title' => $title . ' ' . $capacityGB,
                    'price' => $price,
                    'imageUrl' => $imageUrl,
                    'capacityMB' => $capacityMB,
                    'colour' => $colour,
                    'availabilityText' => $availabilityText,
                    'isAvailable' => $isAvailable,
                    'shippingText' => $shippingText,
                    'shippingDate' => $shippingDate,
                ];
                $this->products[] = new Product($productData);
            }
        });
    }

    /**
     * Builds full image URL from the source.
     *
     * @param string $src
     * @return string
     */
    public function getFullImageUrlFromSource(string $src): string
    {
        $src = str_replace('../', '/', $src);
        return self::IMAGE_BASE_URL . str_replace('\\/', '/', $src);
    }

    /**
     * Extracts the product title.
     *
     * @param Crawler $node
     * @return string
     */
    public function extractTitle(Crawler $node): string
    {
        return trim($node->filter('.product-name')->text());
    }

    /**
     * Extracts the product price.
     *
     * @param Crawler $node
     * @return float
     */
    public function extractPrice(Crawler $node): float
    {
        $priceText = $node->filter('.my-8.text-lg')->text();
        return (float) str_replace(['£', ' '], '', $priceText);
    }

    /**
     * Converts device capacity to MB.
     *
     * @param string $capacityText
     * @return int
     */
    public function convertCapacityToMB(string $capacityText): int
    {
        if (strpos($capacityText, 'GB') !== false) {
            $capacityGB = (int) str_replace('GB', '', $capacityText);
            return $capacityGB * 1024;
        }
        return (int) str_replace('MB', '', $capacityText);
    }

    /**
     * Extracts the availability text.
     *
     * @param Crawler $node
     * @return string
     */
    public function extractAvailabilityText(Crawler $node): string
    {
        $availabilityText = trim($node->filter('.my-4.text-sm.block.text-center')->first()->text());
        // Check if it contains "Availability: "
        if (strpos($availabilityText, 'Availability: ') !== false) {
            return str_replace('Availability: ', '', $availabilityText);
        }
        return $availabilityText; // Return as is if not found
    }

    /**
     * Extracts the shipping text.
     *
     * @param Crawler $node
     * @return string
     */
    public function extractShippingText(Crawler $node): string
    {
        return trim($node->filter('.my-4.text-sm.block.text-center')->last()->text());
    }

    /**
     * Extracts the shipping date from the shipping text.
     *
     * @param Crawler $node
     * @return ?string
     */
    public function extractShippingDate(Crawler $node): ?string
    {
        $shippingText = $this->extractShippingText($node);
        $matches = [];
        if (preg_match('/(\d{1,2}\s\w+\s\d{4})/', $shippingText, $matches)) {
            return date('Y-m-d', strtotime($matches[1]));
        }
        return null;
    }

    /**
     * Saves the extracted products to a JSON file.
     *
     * @param string $filename
     * @return void
     */
    public function saveToFile(string $filename): void
    {
        file_put_contents($filename, json_encode(array_map(fn($product) => $product->toArray(), $this->products), JSON_PRETTY_PRINT));
    }
}

$scrape = new Scrape();
$scrape->run();
