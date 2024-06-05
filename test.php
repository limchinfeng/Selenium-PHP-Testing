<?php

require_once('vendor/autoload.php');

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Test
{
    protected $driver;
    protected $latestProductId;

    public function __construct()
    {
        // Chrome
        $host = 'http://localhost:9515';
        $capabilities = DesiredCapabilities::chrome();

        // Firefox
        // $host = 'http://localhost:4444';
        // $capabilities = DesiredCapabilities::firefox();

        // Microsoft Edge
        // $host = 'http://localhost:9515';
        // $capabilities = DesiredCapabilities::microsoftEdge();

        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    public function __destruct()
    {
        $this->driver->quit();
    }

    public function waitForElement($by, $timeout = 10)
    {
        $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, $timeout);
        return $wait->until(WebDriverExpectedCondition::presenceOfElementLocated($by));
    }

    public function login()
    {
        // $this->driver->get('http://127.0.0.1:8000/login');
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys('test@mail.com');
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('password');
        $this->driver->findElement(WebDriverBy::name('submit'))->click();
        // $this->driver->findElement(WebDriverBy::name('password'))->submit();
    }

    public function testCreate()
    {
        // login and go to create page
        $this->driver->get('http://localhost:8000/products/create');
        $this->login();

        // Generate a random product name
        $productName = 'Product ' . substr(md5(mt_rand()), 0, 7);
        $productDescription = 'Description for ' . $productName;

        $this->driver->findElement(WebDriverBy::name('name'))->sendKeys($productName);
        $this->driver->findElement(WebDriverBy::name('description'))->sendKeys($productDescription);
        $this->driver->findElement(WebDriverBy::name('submit'))->click();

        // $this->waitForElement(WebDriverBy::name('name'))->sendKeys($productName);
        // $this->waitForElement(WebDriverBy::name('description'))->sendKeys($productDescription);
        // $this->waitForElement(WebDriverBy::name('submit'))->click();

        // Fetch the latest product ID
        $this->driver->get('http://localhost:8000/products');
        $productElements = $this->driver->findElements(WebDriverBy::cssSelector('tbody tr'));

        // Check if the product was created successfully (if have the number wont be 0)
        if (count($productElements) == 0)
            echo "Create Test: Failed - No product elements found." . PHP_EOL;

        // Assuming the latest product is the first row
        $latestProductElement = $productElements[0];
        $this->latestProductId = $latestProductElement->getAttribute('id');
        $productNameElement = $latestProductElement->findElement(WebDriverBy::cssSelector('td:nth-child(2)'));
        $fetchedProductName = $productNameElement->getText();
        echo "Generated Product Name: " . $productName . PHP_EOL;
        echo "Fetched Product Name: " . $fetchedProductName . PHP_EOL;

        if ($fetchedProductName === $productName) {
            echo "Create Test: Passed" . PHP_EOL;
        } else {
            echo "Create Test: Failed - Product name does not match." . PHP_EOL;
        }
    }

    public function testEdit()
    {
        // $this->login();

        // Go to the edit page of the latest product
        $this->driver->get('http://localhost:8000/products/' . $this->latestProductId . '/edit');

        // Generate a new random product name
        $updatedProductName = 'Updated ' . substr(md5(mt_rand()), 0, 7);
        sleep(2);
        $this->driver->findElement(WebDriverBy::name('name'))->clear()->sendKeys($updatedProductName);
        $this->driver->findElement(WebDriverBy::name('submit'))->click();
        
         // Fetch the value of the name input field after the update
        //  $this->driver->get('http://localhost:8000/products/' . $this->latestProductId . '/edit');
         $updatedNameInputValue = $this->waitForElement(WebDriverBy::name('name'))->getAttribute('value');
         echo "Updated Product Name: " . $updatedProductName . PHP_EOL;
         echo "Fetched Name Input Value: " . $updatedNameInputValue . PHP_EOL;

         if ($updatedNameInputValue === $updatedProductName) {
             echo "Edit Test: Passed" . PHP_EOL;
         } else {
             echo "Edit Test: Failed - Name input value does not match." . PHP_EOL;
         }
      
    }

    public function testDelete()
    {
        // $this->login();
        $this->driver->get('http://localhost:8000/products');

        // Find the delete button for the latest product and click it
        $deleteButton = $this->waitForElement(WebDriverBy::cssSelector('form[action$="' . $this->latestProductId . '"] button[type="submit"]'));
        $deleteButton->click();

        // Confirm the alert
        // $this->driver->switchTo()->alert()->accept();

        // Refresh the page and check if the product is still present
        $this->driver->get('http://localhost:8000/products');
        $productElements = $this->driver->findElements(WebDriverBy::cssSelector('tbody tr'));

        $productStillExists = false;
        foreach ($productElements as $productElement) {
            if ($productElement->getAttribute('id') === $this->latestProductId) {
                $productStillExists = true;
                break;
            }
        }

        if (!$productStillExists) {
            echo "Delete Test: Passed" . PHP_EOL;
        } else {
            echo "Delete Test: Failed - Product still exists." . PHP_EOL;
        }
    }

    public function testView()
    {
        // $this->login();

        $this->driver->get('http://localhost:8000/products');
        $productElements = $this->driver->findElements(WebDriverBy::cssSelector('tbody tr'));

        // Check if the product was created successfully (if have the number wont be 0)
        if (count($productElements) == 0)
            echo "View Test: Failed - No product elements found." . PHP_EOL;

        // Assuming the latest product is the first row and check whether it got appear in the table
        $latestProductElement = $productElements[0];
        $latestProductIdInTable = $latestProductElement->getAttribute('id');


        if ($latestProductIdInTable === $this->latestProductId) {
            echo "View Test: Passed - Newly Generated Product is shown" . PHP_EOL;
        } else {
            echo "Create Test: Failed - Newly Generated Product does not appear." . PHP_EOL;
        }
    }

    public function testSelenium()
    {
        // $this->login();
        $this->driver->get('https://www.google.com');
        $element = $this->driver->findElement(WebDriverBy::name('q'));
        if ($element) {
            $element->sendKeys('limkimseah.com');
            $element->submit();
            echo "Search completed." . PHP_EOL;
        } else {
            echo "Search box not found." . PHP_EOL;
        }

        sleep(3);

        echo "Search operation in google.com done" . PHP_EOL;
    }
}

$test = new Test();
// $test->testSelenium();
// $test->login();
$test->testCreate();
sleep(1);
$test->testView();
sleep(1);
$test->testEdit();
sleep(2);
$test->testDelete();
sleep(2);
// sleep(3);
