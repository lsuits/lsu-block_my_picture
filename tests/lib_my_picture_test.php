<?php

//defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');
require_once('util.php');


class lib_my_picture_testcase extends advanced_testcase{
    
    
    protected $users_no_pix;
    protected $users_wth_pix;
    protected $users_badids;
    
    protected function setUp(){
        setConfig();
        
        $users_no_pix   =  getUsersWithoutPix();
        $users_wth_pix  = getUsersWithPix();
        $users_badids   = generateUsers(10, 0, true);
        
        $this->users_no_pix    = $this->populateDB($users_no_pix);
        $this->users_wth_pix   =$this-> populateDB($users_wth_pix);
        $this->users_badids    = $this->populateDB($users_badids);
    }
    
    private function populateDB(array $users){
        $db_users = array();    
        foreach($users as $user){
            $db_users[] = $this->getDataGenerator()->create_user($user);
        }
        return $db_users;
    }
    
    /**
     * @testdox Lookup users without pictures
     * 
     * @global type $DB
     * @return type
     */
    public function test_mypic_get_users_without_pictures(){

        global $DB;
        $this->resetAfterTest(true);
        
        $expected = count($DB->get_records('user',array('picture' => 0)));
        $actual = mypic_get_users_without_pictures();
        $this->assertCount($expected, $actual);
        
        return $actual;
    }
    
    

    
    /**
     * @testdox Webservice returns users needing updated pictures
     */
    public function test_mypic_get_users_updated_pictures(){
        $this->resetAfterTest(true);
        
        $users = mypic_get_users_updated_pictures(time()-1000000);
        $this->assertNotEmpty($users, "No results from webservice, check the time() param");
    }
        
    public function test_mypic_update_picture(){
        $this->resetAfterTest(true);
        global $DB;
        $user       = $DB->get_record('user', array('username' => 'jpeak5'));
        $bad_user   = generateUser(null, null, false, true);
        $bad_user   = $this->getDataGenerator()->create_user($bad_user);
        
        $ret = mypic_update_picture($bad_user);
        $this->assertEquals(1, $ret, sprintf("update_picture should have failed for non-existant user, but returned %d for user with idnumber %d", $ret, $user->idnumber));
        
        $ret = mypic_update_picture($user);
        $this->assertEquals(2, $ret, sprintf("update_picture returned %d for user with idnumber %d", $ret, $user->idnumber));
        
        
    }
    
    public function test_mypic_force_update_picture(){
        $this->resetAfterTest(true);
        ;
        $update     = get_config('block_my_picture', 'update_url');
        $this->assertEquals($update, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update');
    }
    

}



?>
