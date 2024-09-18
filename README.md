# Smartphone Scraper

## Overview

This project contains a PHP script for scraping smartphone data from a specified website. The data is fetched, processed, and saved to a JSON file.

## Installation

1. Clone the repository:
   bash
   git clone https://your-repository-url.git
   cd your-repository-directory

2. Install dependencies:
    bash
    composer install

## Running the Scraper
php src/Scrape.php

## Testing
Test cases are written using PHPUnit. To run tests:
    ```bash
    Install Pest if not already installed:
        composer require pestphp/pest --dev
    Initialize the Pest:
        ./vendor/bin/pest --init
    Install Mockery if not already installed:
        composer require --dev mockery/mockery
    Run the tests:
        ./vendor/bin/pest

