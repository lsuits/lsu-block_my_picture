<?php
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');
require_once('util.php');

/**
 * @testdox Reprocess All
 */
class reprocessAll_testcase extends advanced_testcase{
    
//    public function testShouldFail(){
//        $this->assertTrue(false);
//    }
    
    public function testReprocessAll(){
        global $DB;
        $this->resetAfterTest(true);
        $DB->delete_records('user');
        setConfig();
        
        $users = generateUsers(25, 10);
        $this->assertCount(25,$users);
        
        $userdb_users = array();
        foreach($users as $user){
            $db_users[] = $this->getDataGenerator()->create_user($user);
        }
        
        $params = array('deleted' => '0');
        $users = $DB->get_records('user', $params, '', 'id, idnumber');
        
        $force_update = true;
        $return = mypic_batch_update($users, $force_update, '<br />');
        
        $this->assertEquals(0, $return['num_err'], "errors count assertion failed");
        $this->assertEquals(25, $return['num_suc'], "Successful update assertion failed.");
        $this->assertEquals(25, $return['num_nop'], "Nopic assertion failed.");
        
    }
    
    
    
}
?>
