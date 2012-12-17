<?php


defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');


class block_my_picture_testcase extends advanced_testcase {
      
    
    public function testNothing(){
        
        $this->assertTrue(true);
        
    }
    
    
}

?>
