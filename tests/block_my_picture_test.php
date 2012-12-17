<?php


defined('MOODLE_INTERNAL') || die();
global $CFG;
//require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');

class block_my_picture_testcase extends advanced_testcase {

       
    
    
    public function test_cron(){
        global $DB;
//        $this->initialise_cfg();
        
         $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('block_my_picture');
        $this->assertInstanceOf('block_my_picture_generator', $generator);
        $this->assertEquals('my_picture', $generator->get_blockname());
        
        $block = new block_my_picture();
        echo get_class($block);
        $generator->cron();
//        $this->assertInstanceOf('block_my_picture', $block);
        $this->assertTrue(true);
        
//        $generator->create_instance();
//        $generator->create_instance();
//        $bi = $generator->create_instance();
        $this->assertTrue(true);
//        $this->assertEquals($beforeblocks+3, $DB->count_records('block_instances'));
    }
    
    
}

?>
