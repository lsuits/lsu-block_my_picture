<?php

//defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');



class lib_my_picture_testcase extends advanced_testcase{
    
    
    /**
     * @testdox This is only a test
     * @dataProvider userProvider
     * @global type $DB
     * @return type
     */
    public function testMypicGetUsersWithoutPicturesFetchesTheRightNumber($usersSet){

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
    
    
    public function testConfigUrlIsCorrect(){
        global $DB;
        $this->resetAfterTest(true);
              
        $this->setConfig();
        
        $this->expectOutputString('https://tt.lsu.edu/api/v2/json/kZabUZ6TZLcsYsCnV6KW/photos/recently_updated/%s');
        echo get_config('block_my_picture', 'ready_url');
    }
    
    /**
     * @dataProvider userProvider
     */
    public function test_mypic_get_users_updated_pictures($usersSet){

        //set up block configs
        $this->setConfig();
        
        $users = mypic_get_users_updated_pictures(time()-1000000);
        $this->assertNotEmpty($users, "No results from webservice, check the time() param");
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
    

    
    
    
    
    private function setConfig(){
        
        global $DB;
        $this->resetAfterTest(true);
        
        set_config('ready_url', 'https://tt.lsu.edu/api/v2/json/kZabUZ6TZLcsYsCnV6KW/photos/recently_updated/%s', 'block_my_picture');
        set_config('update_url', 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update', 'block_my_picture');
        set_config('webservice_url', 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s?skip_place_holder=true', 'block_my_picture');
        set_config('fetch', 1, 'block_my_picture');
        set_config('cron_users', 10, 'block_my_picture');
    }
}



?>
