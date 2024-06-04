<?php

require_once('vendor/autoload.php');

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;


class Test
{
    protected $driver;

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

    public function login()
    {
        $this->driver->get('http://localhost:8000/login');
        $this->driver->findElement(WebDriverBy::name('email'))->sendKeys('your-email@example.com');
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('your-password');
        $this->driver->findElement(WebDriverBy::name('password'))->submit();
    }

    public function testCreate()
    {
        $this->login();
        $this->driver->get('http://localhost:8000/items/create');
        $this->driver->findElement(WebDriverBy::name('name'))->sendKeys('New Item');
        $this->driver->findElement(WebDriverBy::name('description'))->sendKeys('Item description');
        $this->driver->findElement(WebDriverBy::name('submit'))->click();
        echo "Create Test: " . (strpos($this->driver->getPageSource(), 'Item created successfully') !== false ? "Passed" : "Failed") . PHP_EOL;
    }

    public function testEdit()
    {
        $this->login();
        $this->driver->get('http://localhost:8000/items/1/edit');
        $this->driver->findElement(WebDriverBy::name('name'))->clear()->sendKeys('Updated Item');
        $this->driver->findElement(WebDriverBy::name('submit'))->click();
        echo "Edit Test: " . (strpos($this->driver->getPageSource(), 'Item updated successfully') !== false ? "Passed" : "Failed") . PHP_EOL;
    }

    public function testDelete()
    {
        $this->login();
        $this->driver->get('http://localhost:8000/items');
        $this->driver->findElement(WebDriverBy::cssSelector('a.delete-button'))->click();
        $this->driver->switchTo()->alert()->accept();
        echo "Delete Test: " . (strpos($this->driver->getPageSource(), 'Item deleted successfully') !== false ? "Passed" : "Failed") . PHP_EOL;
    }

    public function testView()
    {
        $this->login();
        $this->driver->get('http://localhost:8000/items/1');
        echo "View Test: " . (strpos($this->driver->getPageSource(), 'Item Details') !== false ? "Passed" : "Failed") . PHP_EOL;
    }

    public function search()
    {
        $this->driver->get('https://www.limkimseah.com');
        // $element = $this->driver->findElement(WebDriverBy::name('q'));
        // if ($element) {
          //     $element->sendKeys('limkimseah.com');
          //     $element->submit();
          //     echo "Search completed." . PHP_EOL;
          // } else {
            //     echo "Search box not found." . PHP_EOL;
            // }
            
            // sleep(3000);
            $this->driver->get('https://www.google.com');
    }
}

$test = new Test();
$test->search();
// $test->testCreate();
// $test->testEdit();
// $test->testDelete();
// $test->testView();
?>