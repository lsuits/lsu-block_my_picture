<?php
global $CFG;
require_once $CFG->dirroot.'/blocks/my_picture/tests/webservices_test.php';
require_once dirname(dirname(__FILE__)).'/lib.php';

class lib_test extends mypic_webservices_testcase {
    
    /**
     * ensure that the tested function properly returns only valid idnumbers
     */
    public function test_mypic_WebserviceIntersectMoodleReturnsValidUsers(){
        $knownUser  = $this->getValidUser();
        $idnumbers  = array($knownUser->idnumber, 'id'.$this->ws->getFakeId(), 'id'.$this->ws->getFakeId());
        $validUsers = mypic_WebserviceIntersectMoodle($idnumbers);
        
        $this->assertNotEmpty($validUsers);
        $this->assertEquals(1, count($validUsers));
        $this->assertTrue(array_key_exists(0, $validUsers));
        $this->assertEquals($knownUser->idnumber, $validUsers[0]->idnumber);
    }

    /**
     * Create a set of users that don't have pictures.
     * ensure that the library function under test returns the 
     * correct number of users without pictures
     * @global type $DB
     */
    public function test_mypic_get_users_without_pictures(){
        global $DB;
        
        //the admin and guest users created by moodle/phpunit 
        //should be excluded from this test
        
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
    
    /**
     * ensure that fn under test returns false given bad input
     */
    public function test_mypic_insert_picture_nofile(){
        $user    = $this->getValidUser();
        $badPath = 'nonexistent/path.jpg';
        
        $this->assertFalse(mypic_insert_picture($user->id, $badPath));
    }

    /**
     * ensure that fn under test returns false given bad input
     */
    public function test_mypic_insert_picture_oneByte(){
        $user     = $this->getValidUser();
        $bytePath = 'oneByte';
        $filesize = file_put_contents($bytePath, " ");
        
        $this->assertFileExists($bytePath);
        $this->assertEquals(1, $filesize);
        $this->assertFalse(mypic_insert_picture($user->id, $bytePath));
    }
    
    /**
     * ensure that fn under test returns true given good input
     */
    public function test_mypic_insert_picture_success(){
        $user     = $this->getValidUser();
        $goodPath = 'tests/mike.jpg';
        
        $this->assertFileExists($goodPath);
        $this->assertTrue(mypic_insert_picture($user->id, $goodPath));
    }
    
    /**
     * ensure that given a bad user idnumber, the fn under test
     * returns the intended integer response
     * @global type $DB
     */
    public function test_mypic_update_picture_badid(){
        $badIdUser = $this->getBadIdUser();
        $this->assertEquals(0,$this->getDbPicStatusForUser($badIdUser));

        //now test function result
        $this->assertEquals(1,mypic_update_picture($badIdUser));
        $this->assertEquals(1,$this->getDbPicStatusForUser($badIdUser));
    }

    /**
     * ensure that given a valid user idnumber, the fn under test
     * returns the intended integer response and that the user object
     * 'picture' attribute is correctly updated in the DB
     * @global type $DB
     */
    public function test_mypic_update_picture_success(){
        $goodUser = $this->getValidUser();
        $this->assertEquals(0,$this->getDbPicStatusForUser($goodUser));
        
        $this->assertEquals(2,mypic_update_picture($goodUser));
        $this->assertEquals(1,$this->getDbPicStatusForUser($goodUser));
    }
    
    /**
     * ensure that given a valid user idnumber, but for which
     * no picture exists in the webservice, the fn under test
     * returns the intended integer response 3 and that the user object
     * 'picture' attribute is correctly updated in the DB
     * @global type $DB
     */
    public function test_mypic_update_picture_nopic(){
        $nopicUser = $this->getNoPicUser();
        $this->assertEquals(0,$this->getDbPicStatusForUser($nopicUser));
        
        $this->assertEquals(3,mypic_update_picture($nopicUser));
        $this->assertEquals(1,$this->getDbPicStatusForUser($nopicUser));
    }
    
    public function test_mypic_batch_update(){
        $users = array(
            $this->getNoPicUser(),
            $this->getBadIdUser(),
            $this->getValidUser()
                );
        $result = mypic_batch_update($users);
        
        $this->assertEquals(3, $result['count']);
        $this->assertEquals(1, $result['badid']);
        $this->assertEquals(1, $result['nopic']);
        $this->assertEquals(1, $result['success']);
        
    }
    
    /**
     * @expectedException     coding_exception
     */
    public function test_mypic_verifyWebserviceExists(){
        mypic_force_update_picture(123);
    }
    
}
?>
