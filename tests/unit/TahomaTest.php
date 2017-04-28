<?php
use PHPUnit\Framework\TestCase;

/**
 * Class TahomaTest for testing tahoma code
 */
class TahomaTest extends TestCase {

    /**
     * Test to assert true
     */
    public function testInitialize() {
        $myHome = new TahomaController();
        $this->assertInstanceOf('TahomaController', $myHome);
    }
}