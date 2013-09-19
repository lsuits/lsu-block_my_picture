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
    
    public function test_mypic_get_users_without_pictures(){
        global $DB;
        
        //the admin and guest users created by moodle/phpunit 
        //should be excluded from our test
        
        $defaultUsers = $DB->get_records('user');
        $this->assertEquals(2, count($defaultUsers));
        
        foreach($defaultUsers as $def){
            $def->picture = 1;
            $DB->update_record('user', $def);
        }

        $validIds = $this->ws->getValidUserIds();
        foreach($validIds as $id){
            $this->generateUser(array(
                'idnumber'  => $id,
                'picture'   => 0,
                ));
        }
        $this->assertEquals(count($validIds), count(mypic_get_users_without_pictures()));
    }
    
    public function test_mypic_insert_picture(){
        $user = $this->insertKnownUserIntoMoodle();
        
        //file doesn't exist
        $badPath = 'nonexistent/path.jpg';
        $this->assertFalse(mypic_insert_picture($user->id, $badPath));
        
        //file is only 1 byte
        $bytePath = 'oneByte';
        $filesize = file_put_contents($bytePath, " ");
        $this->assertFileExists($bytePath);
        $this->assertEquals(1, $filesize);
        $this->assertFalse(mypic_insert_picture($user->id, $bytePath));
        
        //file exists
        $goodPath = 'tests/mike.jpg';
        $this->assertFileExists($goodPath);
        $this->assertTrue(mypic_insert_picture($user->id, $goodPath));
    }
    
    public function test_mypic_update_picture_badid(){
        global $DB;
        
        $badIdnumber = 'nosuchidnumberwilleverexist';
        
        $noIdnumberUser = $DB->get_record('user', array('firstname'=>'admin'));
        $this->assertEmpty($noIdnumberUser->idnumber);
        
        $noIdnumberUser->picture = 0;
        $noIdnumberUser->idnumber = $badIdnumber;

        $DB->update_record('user', $noIdnumberUser);
        unset($noIdnumberUser);
        
        $badIdUser = $DB->get_record('user', array('firstname'=>'admin'));
        $this->assertEquals($badIdnumber, $badIdUser->idnumber);
        $this->assertEquals(0,$badIdUser->picture);

        //now test function result
        $this->assertEquals(1,mypic_update_picture($badIdUser));
        $this->assertEquals(0,$badIdUser->picture);
    }
    
    public function test_mypic_update_picture_success(){
        $goodUser = $this->insertKnownUserIntoMoodle();
        $this->assertEquals(0,$goodUser->picture);
        $this->assertEquals(2,mypic_update_picture($goodUser));
    }
    
    /**
     * @TODO finish this test
     */
    public function test_mypic_update_picture_nopic(){
        $nopicUser = $this->generateUser(array(
            'idnumber'  => $this->ws->getIdnumberWithoutPicture(),
            'picture'   => 0
        ));
        $this->assertEquals(3,mypic_update_picture($nopicUser));
    }
    
}
?>
