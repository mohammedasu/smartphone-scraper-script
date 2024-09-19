<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{

    public const PRODUCT_NAME_SELECTOR = '.product-name';
    public const PRODUCT_PRICE_SELECTOR = '.my-8.text-lg';
    public const PRODUCT_CAPACITY_SELECTOR = '.product-capacity';
    public const PRODUCT_IMAGE_SELECTOR = 'img';
    public const AVAILABILITY_SELECTOR = '.my-4.text-sm.block.text-center';
    public const SHIPPING_SELECTOR = '.my-4.text-sm.block.text-center';
    public const IMAGE_BASE_URL = 'https://www.magpiehq.com/developer-challenge';
    
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    /**
     * Extracts the product title from the provided node.
     *
     * @param Crawler $node The DOM node representing the product.
     * @return string The extracted product title.
     */
    public static function extractTitle(Crawler $node): string
    {
        return trim($node->filter(self::PRODUCT_NAME_SELECTOR)->text());
    }

    /**
     * Extracts the product price from the provided node.
     *
     * @param Crawler $node
     * @return float The extracted product price
     */
    public static function extractPrice(Crawler $node): float
    {
        $priceText = $node->filter(self::PRODUCT_PRICE_SELECTOR)->text();
        return (float) str_replace(['£', ' '], '', $priceText);
    }

    /**
     * Builds full image URL from the source.
     *
     * @param string $src
     * @return string
     */
    public static function getFullImageUrlFromSource(Crawler $node): string
    {
        $src = $node->filter(self::PRODUCT_IMAGE_SELECTOR)->attr('src');
        return self::IMAGE_BASE_URL . str_replace('..', '', $src);
    }

    /**
     * Converts device capacity to MB.
     *
     * @param string $capacityText
     * @return int
     */
    public static function convertCapacityToMB(Crawler $node): int
    {
        $capacityText = $node->filter(self::PRODUCT_CAPACITY_SELECTOR)->text();
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
    public static function extractAvailabilityText(Crawler $node): string
    {
        $availabilityText = trim($node->filter(self::AVAILABILITY_SELECTOR)->first()->text());
        return strpos($availabilityText, 'Availability: ') !== false ? str_replace('Availability: ', '', $availabilityText) : $availabilityText;
    }

    /**
     * Extracts the shipping text.
     *
     * @param Crawler $node
     * @return string
     */
    public static function extractShippingText(Crawler $node): string
    {
        return trim($node->filter(self::SHIPPING_SELECTOR)->last()->text());
    }

    /**
     * Extracts the shipping date from the shipping text.
     *
     * @param Crawler $node
     * @return ?string
     */
    public static function extractShippingDate(Crawler $node): ?string
    {
        $shippingText = self::extractShippingText($node);
        
        // Match ISO date format (YYYY-MM-DD)
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $shippingText, $matches)) {
            return $matches[0];
        }

        // Match descriptive date formats
        if (preg_match('/(\d{1,2}(?:st|nd|rd|th)? \w+ \d{4})/', $shippingText, $matches) ||
            preg_match('/(\d{1,2} \w+ \d{4})/', $shippingText, $matches)) {
            return date('Y-m-d', strtotime($matches[1]));
        }

        // Handle relative dates like "tomorrow"
        if (stripos($shippingText, 'tomorrow') !== false) {
            return date('Y-m-d', strtotime('tomorrow')); // Return tomorrow's date
        }

        return null;
    }
}
