<?php

use App\Scrape;
use App\ScrapeHelper;
use Symfony\Component\DomCrawler\Crawler;

beforeEach(function () {
    // instance of the Scrape class
    $this->scrape = new Scrape();
});

afterEach(function () {
    // Clean up Mockery after each test
    \Mockery::close();
});

it('builds the correct URL for pagination', function () {
    $page = 2;
    $url = $this->scrape->buildUrl(Scrape::BASE_URL, $page);
    expect($url)->toBe(Scrape::BASE_URL . '?page=' . $page);
});

it('correctly extracts the product title', function () {
    $node = new Crawler('<div class="product"><span class="product-name">Test Product</span></div>');
    $title = ScrapeHelper::extractTitle($node);
    expect($title)->toBe('Test Product');
});

it('correctly extracts the product price', function () {
    $node = new Crawler('<div class="product"><span class="my-8 text-lg">£499.99</span></div>');
    $price = ScrapeHelper::extractPrice($node);
    expect($price)->toBe(499.99);
});

it('converts GB to MB correctly', function () {
    $capacityMB = ScrapeHelper::convertCapacityToMB('32GB');
    expect($capacityMB)->toBe(32768);
});

it('handles availability text extraction', function () {
    $node = new Crawler('<div class="product"><span class="my-4 text-sm block text-center">Availability: In Stock</span></div>');
    $availabilityText = ScrapeHelper::extractAvailabilityText($node);
    expect($availabilityText)->toBe('In Stock');
});

it('extracts shipping date correctly', function () {
    $shippingDate = ScrapeHelper::extractShippingDate('Shipping by 12 September 2024');
    expect($shippingDate)->toBe('2024-09-12');
});

it('saves data to a JSON file', function () {
    //mock the Product class and its toArray method
    $mockProduct = \Mockery::mock('App\Product');
    $mockProduct->shouldReceive('toArray')->andReturn(['title' => 'Test Product']);
    
    // Manually inject a mock product into the Scrape class
    $this->scrape->products[] = $mockProduct;
    
    // Call the saveToFile method
    $this->scrape->saveToFile('output.json');
    
    // Assert that the file was created and contains expected data
    $this->assertFileExists('output.json');
    $content = file_get_contents('output.json');
    expect($content)->toContain('Test Product');
    
    // Clean up
    unlink('output.json');
});
