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

    /* 
    * Set the host and capabilities for the WebDriver
    **/
    public function __construct()
    {
        // Chrome
        $host = 'http://localhost:9515';
        $capabilities = DesiredCapabilities::chrome();

        /** Firefox */
        // $host = 'http://localhost:4444';
        // $capabilities = DesiredCapabilities::firefox();

        /** Microsoft Edge */
        // $host = 'http://localhost:9515';
        // $capabilities = DesiredCapabilities::microsoftEdge();

        // Create a new instance of the RemoteWebDriver
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }


    public function __destruct()
    {
        $this->driver->quit();
    }


    public function waitForElement($by, $timeout = 10)
    {
        /** Create a new WebDriverWait with the specified timeout */
        $wait = new \Facebook\WebDriver\WebDriverWait($this->driver, $timeout);
        
        /** Wait until the element located by the specified locator is present */
        return $wait->until(WebDriverExpectedCondition::presenceOfElementLocated($by));
    }


    public function login()
    {
        /** Uncomment it if you want to go to the route */
        // $this->driver->get('http://127.0.0.1:8000/login


        /** Find the email input field and enter the email */
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys('test@mail.com');
        
        /** Find the password input field and enter the password */
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('password');
        
        /** Find the submit button and click it */
        $this->driver->findElement(WebDriverBy::name('submit'))->click();
    }


    public function testCreate()
    {
        /** Go to the create page */
        $this->driver->get('http://localhost:8000/products');

        /** if there is a middleware, call the login function */
        $this->login();

        /** Click on the "Create Product" button */
        $this->driver->findElement(WebDriverBy::linkText('Create Product'))->click();

        /** Generate a random product name and description */
        $productName = 'Product ' . substr(md5(mt_rand()), 0, 7);
        $productDescription = 'Description for ' . $productName;

        /** Fill in the name and description and click the submit button */
        $this->driver->findElement(WebDriverBy::name('name'))->sendKeys($productName);
        $this->driver->findElement(WebDriverBy::name('description'))->sendKeys($productDescription);
        $this->driver->findElement(WebDriverBy::name('submit'))->click();

        /** example of how waitForElement Funciton works */
        // $this->waitForElement(WebDriverBy::name('name'))->sendKeys($productName);
        // $this->waitForElement(WebDriverBy::name('description'))->sendKeys($productDescription);
        // $this->waitForElement(WebDriverBy::name('submit'))->click();

        /** Check for the success message  */
        $successMessage =  $this->driver->findElement(WebDriverBy::cssSelector('.bg-amber-500'))->getText();

        /** Check if the success message contains the expected text */
        if ($successMessage === 'Product added successfully') {
            echo "Success Message Test: Passed" . PHP_EOL;
        } else {
            echo "Success Message Test: Failed - Message not found or text does not match." . PHP_EOL;
        }

        /** Get the table rows */
        // $this->driver->get('http://localhost:8000/products');
        $productElements = $this->driver->findElements(WebDriverBy::cssSelector('tbody tr'));


        /** Check if the product was created successfully */
        if (count($productElements) == 0)
            echo "Create Test: Failed - No product elements found." . PHP_EOL;

        /** Assuming the latest product is the first row and check whether it got created successfully */
        $latestProductElement = $productElements[0];
        $this->latestProductId = $latestProductElement->getAttribute('id');
        $productNameElement = $latestProductElement->findElement(WebDriverBy::cssSelector('td:nth-child(2)'));
        $fetchedProductName = $productNameElement->getText();
        echo "Generated Product Name: " . $productName . PHP_EOL;
        echo "Fetched Product Name: " . $fetchedProductName . PHP_EOL;

        if ($fetchedProductName === $productName ) {
            echo "Create Test: Passed" . PHP_EOL;
        } else {
            echo "Create Test: Failed - Product name does not match." . PHP_EOL;
        }
    }


    public function testEdit()
    {
        /** Method 1: Go to the edit page of the latest product */
        // $this->driver->get('http://localhost:8000/products/' . $this->latestProductId . '/edit');

        /** Method 2 : Find the row with the latest product ID and click the edit link */
        // $productRow = $this->driver->findElement(WebDriverBy::cssSelector("tr[id='{$this->latestProductId}']"));
        // $productRow->findElement(WebDriverBy::cssSelector("a[id='edit-{$this->latestProductId}']"))->click();


        /** Method 3: Get the table rows */
        $productElements = $this->driver->findElements(WebDriverBy::cssSelector('tbody tr'));

        /** Find the row with the product name */
        $productRow = null;
        foreach ($productElements as $element) {
            $productIdElement = $element->findElement(WebDriverBy::cssSelector('td:nth-child(1)'));
            if ($productIdElement->getText() === $this->latestProductId) {
                $productRow = $element;
                break;
            }
        }

        if ($productRow === null) {
            echo "Edit Test: Failed - Product not found." . PHP_EOL;
            return;
        }

        /** Click the edit link for that row */
        // $productRow->findElement(WebDriverBy::cssSelector("a[id^='edit-']"))->click();
        $productRow->findElement(WebDriverBy::linkText('Edit'))->click();


        // --------------------------------------------------------------------------------------------
        //  Edit the product name
        // --------------------------------------------------------------------------------------------

        /** Generate a new random product name */
        $updatedProductName = 'Updated ' . substr(md5(mt_rand()), 0, 7);
        sleep(2);
        $this->driver->findElement(WebDriverBy::name('name'))->clear()->sendKeys($updatedProductName);
        $this->driver->findElement(WebDriverBy::name('submit'))->click();
        
        /** Fetch the value of the name input field after the update */
        //  $this->driver->get('http://localhost:8000/products/' . $this->latestProductId . '/edit'); 
        $updatedNameInputValue = $this->waitForElement(WebDriverBy::name('name'))->getAttribute('value');
        echo "Updated Product Name: " . $updatedProductName . PHP_EOL;
        echo "Fetched Name Input Value: " . $updatedNameInputValue . PHP_EOL;

        /** Check if the latest name of the product is same with the updatedProductName */
        if ($updatedNameInputValue === $updatedProductName) {
            echo "Edit Test: Passed" . PHP_EOL;
        } else {
            echo "Edit Test: Failed - Name input value does not match." . PHP_EOL;
        }
    }


    public function testDelete()
    {
        $this->driver->get('http://localhost:8000/products');

        /** Find the delete button for the latest product and click it */
        $deleteButton = $this->waitForElement(WebDriverBy::cssSelector('form[action$="' . $this->latestProductId . '"] button[type="submit"]'));
        $deleteButton->click();

        /** If there is an alert and need confirmation */
        // $this->driver->switchTo()->alert()->accept();

        /** Refresh the page and check if the product is still present */
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
        /** Go to the products page */
        $this->driver->get('http://localhost:8000/products');
        $productElements = $this->driver->findElements(WebDriverBy::cssSelector('tbody tr'));

        /** Check if the product was created successfully */
        if (count($productElements) == 0)
            echo "View Test: Failed - No product elements found." . PHP_EOL;

        /** Assuming the latest product is the first row and check whether it got appear in the table */
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
        $this->driver->get('https://www.google.com');
        $element = $this->driver->findElement(WebDriverBy::name('q'));
        if ($element) {
            $element->sendKeys('limkimseah.com');
            $element->submit();
            echo "Search completed." . PHP_EOL;
        } else {
            echo "Search box not found." . PHP_EOL;
        }

        echo "Search operation in google.com done" . PHP_EOL;
        sleep(3);
    }
}

$test = new Test();
// $test->testSelenium();
// $test->login();
$test->testCreate();
// sleep(1);
// $test->testView();
// sleep(1);
$test->testEdit();
// sleep(2);
// $test->testDelete();
// sleep(2);
