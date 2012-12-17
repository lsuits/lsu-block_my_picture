<?php

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');



class lib_my_picture_testcase extends advanced_testcase{
    
    public function test_mypic_get_users_without_pictures(){
        global $DB;
        $this->resetAfterTest(true);
        
//        $DB->update_record('user', $user, false);
        
        
        
        $expected = count($DB->get_record_sql('SELECT count(`username`) FROM {user} WHERE picture = 0'));
        $actual = mypic_get_users_without_pictures();
        $this->assertCount($expected, $actual);
    }
    
}

?>
