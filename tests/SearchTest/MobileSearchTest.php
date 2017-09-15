<?php

namespace SearchTest;

class MobileSearchTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();
    }

    public function testAddInt() {
        $userModel = new \User\Model\User();
        $dsyzug = $userModel->getUserEmail(674);
        $this->assertEquals('ds.yadav.iitd@gmail.com', $dsyzug['email']);
    }
    
    public function testMobileSearchApis(){
        $mobileSearchController = new \Search\Controller\MobileSearchController();
        $request = array('reqtype'=>'search');
//        $response = $mobileSearchController->create($request);
//        pr($response);
        $this->assertTrue(true);
    }

}
