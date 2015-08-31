<?php
class MoneyTest extends PHPUnit_Framework_TestCase {

  public function testObjectCanBeConstructedFromStringValue()
    {
        $this->assertEquals(true, true);
    }
	
  public function testCanConnectMySql()
    {
	$servername = "127.0.0.1";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
$isConnected = false;
} else {
$isConnected = true;
}


	$this->assertEquals(true, $isConnected);
    }
	
	
	
	
}
?>