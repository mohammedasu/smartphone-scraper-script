# Smartphone Scraper

## Overview

This project contains a PHP script for scraping smartphone data from a specified website. The data is fetched, processed, and saved to a JSON file.

### Requirements

* PHP 7.4+
* Composer

### Setup

```
git clone https://github.com/mohammedasu/smartphone-scraper-script.git
cd smartphone-scraper-script
composer install
```

To run the scrape you can use `php src/Scrape.php`
```

### Testing

* Test cases are written using Pest. To run tests:
    ```
    Install Pest if not already installed:
        composer require pestphp/pest --dev
    Initialize the Pest:
        ./vendor/bin/pest --init
    Install Mockery if not already installed:
        composer require --dev mockery/mockery
    Run the tests:
        ./vendor/bin/pest
    ```

