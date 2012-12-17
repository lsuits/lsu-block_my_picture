<?php
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');
require_once('util.php');


class reprocess_testcase extends advanced_testcase{
    
    /**
     * @TODO develop a method by which to test the case where a user has no picture
     * 
     */
    public function testReprocess(){
        global $DB;
        $this->resetAfterTest(true);
        $DB->delete_records('user');
        setConfig();
        
        $bad_id_users = generateUsers(10,0, true);
        foreach($bad_id_users as $user){
            $u = $this->getDataGenerator()->create_user($user);
            $reprocess = mypic_update_picture($u);
            $this->assertTrue(is_numeric($reprocess));
            $this->assertEquals(1, $reprocess);
        }
        
        $users_w_pix = getUsersWithPix();
        foreach($users_w_pix as $user){
            $u = $this->getDataGenerator()->create_user($user);
            $reprocess = mypic_update_picture($u);
            $this->assertTrue(is_numeric($reprocess));
            $this->assertEquals(2, $reprocess); 
        }

//        $users_wo_pix = getUsersWithoutPix();
//        foreach($users_wo_pix as $user){
//            $u = $this->getDataGenerator()->create_user($user);
//            mtrace($u->idnumber);
//            $reprocess = mypic_update_picture($u);
//            $this->assertTrue(is_numeric($reprocess));
//            $this->assertEquals(3, $reprocess); 
//        }
        
//        $user = $this->getDataGenerator()->create_user(generateUser('nopic', 890775049,false));
//        $reprocess = mypic_update_picture($user);
//        $this->assertTrue(is_numeric($reprocess));
//        $this->assertEquals(3, $reprocess);
        
    }
    
    
}
?>
