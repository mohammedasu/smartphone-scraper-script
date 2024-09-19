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

    /**
     * @var Product[] $products
     */
    public array $products = [];

    /**
     * @var array<string, bool> $productIds
     */
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
        $title = ScrapeHelper::extractTitle($node);
        $price = ScrapeHelper::extractPrice($node);
        $imageUrl = ScrapeHelper::getFullImageUrlFromSource($node);
        $capacityMB = ScrapeHelper::convertCapacityToMB($node);
        $capacityGB = $node->filter('.product-capacity')->text();

        $node->filter('span[data-colour]')->each(function (Crawler $colourNode) use ($node, $title, $price, $imageUrl, $capacityMB, $capacityGB) {
            $colour = $colourNode->attr('data-colour');
            $availabilityText = ScrapeHelper::extractAvailabilityText($node);
            $isAvailable = strpos($availabilityText, 'In Stock') !== false;
            $shippingText = ScrapeHelper::extractShippingText($node);
            $shippingDate = ScrapeHelper::extractShippingDate($node);

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

// Run the scraper
$scrape = new Scrape();
$scrape->run();
