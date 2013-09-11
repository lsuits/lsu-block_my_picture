<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/moodleblock.class.php');
require_once dirname(dirname(__FILE__)).'/lib.php';
require_once dirname(dirname(__FILE__)).'/block_my_picture.php';

class mypic_block_testcase extends advanced_testcase {
    
    public function test_tsStartTimeForRecentUpdates(){
        $block = new block_my_picture();
        $this->assertInternalType('integer', $block->tsStartTimeForRecentUpdates());
    }
    
}
?>
