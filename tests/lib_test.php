<?php
require_once 'tests/webservices_test.php';
require_once dirname(dirname(__FILE__)).'/lib.php';

class lib_test extends mypic_webservices_testcase {
    
    public function test_mypic_WebserviceIntersectMoodleReturnsValidUsers(){
        $knownUser  = $this->insertKnownUserIntoMoodle();
        $idnumbers  = array($knownUser->idnumber, 'id'.$this->fakeId(), 'id'.$this->fakeId());
        $validUsers = mypic_WebserviceIntersectMoodle($idnumbers);
        
        $this->assertNotEmpty($validUsers);
        $this->assertEquals(1, count($validUsers));
        $this->assertTrue(array_key_exists(0, $validUsers));
        $this->assertEquals($knownUser->idnumber, $validUsers[0]->idnumber);
    }
    
    private function fakeId(){
        return rand(1234567, 9876543);
    }
    
}
?>
