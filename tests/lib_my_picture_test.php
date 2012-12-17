<?php

//defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');
require_once('util.php');


class lib_my_picture_testcase extends advanced_testcase{
    
    
    /**
     * @testdox Lookup users without pictures
     * @dataProvider userProvider
     * @global type $DB
     * @return type
     */
    public function test_mypic_get_users_without_pictures($usersSet){

        global $DB;
        $this->resetAfterTest(true);
        $DB->delete_records('user');
        
        //empty user table
        $no_users = $DB->get_records('user');
        $this->assertEquals(0, count($no_users));
        
        //create test users
        foreach($usersSet as $user){
            $this->getDataGenerator()->create_user($user);
        }
        
        //verify that we created the correct number of test users
        $test_users = $DB->get_records('user');
        $this->assertEquals(count($usersSet), count($test_users));

        
        $expected = count($DB->get_records('user',array('picture' => 0)));
        $actual = mypic_get_users_without_pictures();
        $this->assertCount($expected, $actual);
        
        return $actual;
    }
    
    
    /**
     * @testdox block config settings are correct
     * @global type $DB
     */
    public function testConfigUrlIsCorrect(){
        global $DB;
        $this->resetAfterTest(true);
              
        setConfig();
                
        $ready      = get_config('block_my_picture', 'ready_url');
        $update     = get_config('block_my_picture', 'update_url');
        $webservice = get_config('block_my_picture', 'webservice_url');
        
        $this->assertEquals($update, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update');
        $this->assertEquals($webservice, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s?skip_place_holder=true');
        $this->assertEquals($ready, 'https://tt.lsu.edu/api/v2/json/kZabUZ6TZLcsYsCnV6KW/photos/recently_updated/%s');
        
    }
    
    /**
     * @testdox Webservice returns users needing updated pictures
     * @dataProvider userProvider
     */
    public function test_mypic_get_users_updated_pictures($usersSet){
        $this->resetAfterTest(true);
        //set up block configs
        setConfig();
        
        $users = mypic_get_users_updated_pictures(time()-1000000);
        $this->assertNotEmpty($users, "No results from webservice, check the time() param");
    }
        
    
//    /**
//     * @testdox DB Insert Picture
//     * 
//     */
//    public function test_mypic_insert_picture(){
//        setConfig();
//        global $CFG;
//        global $DB;
//        $this->resetAfterTest(true);
//        $user = generateUser('jpeak5', 890775049,false);
//        $this->assertTrue(is_array($user));
//        
//        
//        $path = $CFG->dataroot;
//        $this->getDataGenerator()->create_user($user);
//        $insert = $DB->get_record('user', array('idnumber' => 890775049));
//        $this->assertNotEmpty($insert);
//        $this->assertTrue(is_number($insert->id), sprintf("id for user just inserted is not numeric!"));
//        
//        
//        $bool = mypic_insert_picture($insert->id, $path);
//        $this->assertTrue($bool, sprintf("Inserting picture for user with id = %d, and idnumber %d failed!", $insert->id, $insert->idnumber));
//        
//    }
    
    public function test_mypic_update_picture(){
        $this->resetAfterTest(true);
        setConfig();
        $user       = $this->getDataGenerator()->create_user(generateUser('jpeak5', 890775049,false));
        $bad_user   = $this->getDataGenerator()->create_user(generateUser('asdf',   126575049,false));
        
        $ret = mypic_update_picture($bad_user);
        $this->assertEquals(1, $ret, sprintf("update_picture should have failed for non-existant user, but returned %d for user with idnumber %d", $ret, $user->idnumber));
        
        $ret = mypic_update_picture($user);
        $this->assertEquals(2, $ret, sprintf("update_picture returned %d for user with idnumber %d", $ret, $user->idnumber));
        
        
    }
    
    public function test_mypic_force_update_picture(){
        $this->resetAfterTest(true);
        setConfig();
        $update     = get_config('block_my_picture', 'update_url');
        $this->assertEquals($update, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update');
    }
    
    /**
     * 
     * @return array sets of user arrays
     * NOTE: these users will have picture field set to 0
     */
    public function userProvider(){
        
        $users = array(
            array(
                'username'  =>  'jamsulli',
                'idnumber'  =>  '892300444',
                'picture'   =>  0
            ),
            array(
                'username'  =>  'jpeak5',
                'idnumber'  =>  '890775049',
                'picture'   =>  0
            ),
            array(
                'username'  =>  'hkelly1',
                'idnumber'  =>  '895039221',
                'picture'   =>  0
            ),
            array(
                'username'  =>  'gcole',
                'idnumber'  =>  '891223883',
                'picture'   =>  1
            ),
            array(
                'username'  =>  'aaugui1',
                'idnumber'  =>  '893570546',
                'picture'   =>  1
            ),
            array(
                'username'  =>  'aaust11',
                'idnumber'  =>  '895212274',
                'picture'   =>  1
            )
        );
        return array($users);
    }
    


    

    

    
    
    

}



?>
