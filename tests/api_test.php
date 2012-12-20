<?php
global $CFG;
require_once(dirname(__FILE__) . '/../../moodleblock.class.php');
require_once(dirname(__FILE__) . '/../block_my_picture.php');
require_once(dirname(__FILE__) . '/../lib.php');
require_once('util.php');


class api_testcase extends advanced_testcase{
    
    protected $user_idnumbers;
    protected function setUp(){
        setConfig();
        
        /**
         * idnumbersw ending in 0,1 are Mike The Tiger
         * ending in 9 is a user (Bill Cat) that does not exist in the t-card system
         */
        $this->user_idnumbers = array(
//            890000000,
//            890000001,
//            890000009,
            890001202,
            890001366,
            890001632
            );
    }
        
    /**
     * @testdox block config settings are correct
     * @global type $DB
     */
    public function testConfigUrlIsCorrect(){
        global $DB;
        $this->resetAfterTest(true);
        mtrace("Confirming config values for webservice URLs...\n");
        $ready      = get_config('block_my_picture', 'ready_url');
        $update     = get_config('block_my_picture', 'update_url');
        $webservice = get_config('block_my_picture', 'webservice_url');
        
        $this->assertEquals($update,     'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update');
        $this->assertEquals($webservice, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s?skip_place_holder=true');
        $this->assertEquals($ready,      'https://tt.lsu.edu/api/v2/json/kZabUZ6TZLcsYsCnV6KW/photos/recently_updated/%s');
        
        //output
        mtrace(sprintf("ready_url,      corresponds to webservice 'recently_updated' method/URL is set as   %s", $ready));
        mtrace(sprintf("update_url,     corresponds to webservice 'update'           method/URL is set as   %s", $update));
        mtrace(sprintf("webservice_url, corresponds to webservice 'show'             method/URL is set as   %s", $webservice));
        mtrace("\n--\n");
    }
    
    /**
     * This test method corresponds to the API for queueing a user photo as ready 
     * for reprocessing.
     * NB: <em>This method shedules a process to find any copy the user's photo 
     * from another source. As a result, there is a delay between the time this 
     * request is issued and the updated photo is available.</em>
     * 
     * @global type $CFG
     */
//    public function testTriggerUpdateForSampleUsers(){
//        global $CFG;
//        $this->resetAfterTest(true);
//        $success = array();//we need to know what was scheduled successfully
//        foreach($this->user_idnumbers as $idnumber){
//
//            mtrace(sprintf("Triggering update for user with ID %s from update URL\n", $idnumber));
//
//            $url = get_config('block_my_picture', 'update_url');
//
//            $hash = hash("sha256", $idnumber);
//            $curl = new curl();
//            $json = $curl->post(sprintf($url, $hash));
//
//            $obj = json_decode($json);
//
//            mtrace(sprintf("request url: %s\n", sprintf($url, $hash)));
//
//            mtrace("\nRAW JSON:\n");
//            mtrace(print_r($json));
//
//    //        mtrace("\nJSON decoded:\n");
//    //        mtrace(print_r($obj));
//
//            $this->assertTrue(is_object($obj), sprintf("expected an object, got something else...\n"));
//            $this->assertNotEmpty($obj);
//            
//            if(object_property_exists($obj, 'success')){
//                $this->assertEquals(true, $obj->success->status);
//                $this->assertEquals('Photo update scheduled', $obj->success->message);
//                $success[] = $obj;
//            }else if($obj->error){
//                $this->assertEquals('User not found', $obj->error);
//            }
//            mtrace(sprintf("Updates successfully triggered for %d users", count($success)));
//            mtrace("\n--\n");
//        }
//        return $success;
//    }
    
    public function testGetImageFromWebserviceUrl(){
        global $CFG;
        $this->resetAfterTest(true);
        
        mtrace("Getting images for sample idnumbers...\n");
        foreach($this->user_idnumbers as $idnumber){

            $hash = hash("sha256", $idnumber);

            $filename = $idnumber . '.jpg';
            $fullpath = $CFG->dataroot . '/temp/' . $filename;
            $fp = fopen($fullpath, 'w');

            $curl = new curl();

            $url = sprintf(get_config('block_my_picture', 'webservice_url'), $hash);
            $curl->download(array(array('url' => $url, 'file' => $fp)));
            mtrace(sprintf("%s - %s\n", $idnumber, $url));

            fclose($fp);
            $this->assertGreaterThan(1, filesize($fullpath), sprintf("filesize is zero for path %s", $fullpath));
        }
        mtrace("\n--\n");
        
        
    }
    
    


    
    /**
     * This test mimics a check for recently updated photos.
     * <strong>Be sure</strong> to supply a reasonably small time value (1 day, 
     * for example) to prevent a very long-running test.
     * The resulting array of user objects will be passed on to other dependent 
     * tests, simulating the cron() function in block_my_picture.php
     * @global type $CFG
     * @return type
     */
//    public function testListPhotosRecentlyUpdatedNaturally(){
//
//        global $CFG;
//        $this->resetAfterTest(true);
//        
//        
//        $time = time() - 3600*24*7;
//        mtrace(sprintf("Getting a list of photos updated since %s...", strftime('%F %T', $time)));
//        
//        $result = mypic_get_users_updated_pictures($time);
//        $this->assertTrue(is_array($result));
//        $this->assertNotEmpty($result);
//        
////        mtrace(print_r($result));
//        $limit = 10;
//        mtrace(sprintf("got %s users, listing the first %d...\n", count($result), $limit));
//        $i=0;
//        
//        foreach($result as $user){
//            echo $user->idnumber." ";
//            if($i>$limit){
//                break;
//            }
//            $i++;
//        }
//        mtrace("\n--\n");
//        return($result);
//    }
    

    /**
     * @depends testGetImageFromWebserviceUrl
     * @global type $CFG
     * @return type
     */
//    public function testListPhotosRecentlyUpdatedByUs($updated){
//
//        global $CFG;
//        $this->resetAfterTest(true);
//        
//        if($updated){
//
//            $time = time() - 120;
//            mtrace(sprintf("Getting a list of photos updated updated BY THIS TEST SUITE at %s; expect  %s...\n", strftime('%F %T', $time), count($this->user_idnumbers)));
//            $result = mypic_get_users_updated_pictures($time);
//            $this->assertTrue(is_array($result));
//            $this->assertNotEmpty($result);
//            
//        
//            $updated_ids = array();
//            if($result){
//                foreach($result as $user){
//                    $updated_ids[] = $user->idnumber;
//                }
//            }
//            foreach($this->user_idnumbers as $preset){
//                if(in_array($preset, $updated_ids)){
//                    unset($preset);
//                }
//            }
//            
//            foreach($result as $user){
//             
//                echo $user->idnumber." ";
//                
//            }
//            $this->assertCount(0,$this->user_idnumbers);
//    //        mtrace(print_r($result));
//            mtrace("\n--\n");
//            
//        }
//    }
    
    

    
    
    
    /**
     * 
     * @global type $CFG
     * @return type
     */
    public function testListPhotosRecentlyUpdated(){

        global $CFG;
        $this->resetAfterTest(true);
        $offset = 0;
        
        mtrace("Getting lists of users updated in the last 12 hours");
        
     

            $offset < 12*3600;
            $time = time() - $offset;

            $result = mypic_get_users_updated_pictures($time);
            mtrace(sprintf("\nGetting a list of photos updated updated since %s...got %s\n", strftime('%F %T', $time), count($result)));
            $this->assertTrue(is_array($result));
//            $this->assertNotEmpty($result);
            $res_ids = array();
            foreach($result as $user){
                echo $user->idnumber." ";
                $res_ids[] = $user->idnumber;
            }
            mtrace("\nsample user ids are as follows");
            foreach($this->user_idnumbers as $id){
                echo $id." ";
            }
            
            
        
//        foreach($result as $obj){
//            $this->assertTrue(in_array($obj->idnumber,$this->user_idnumbers));
//            
//        }
        
        mtrace("\ndiff between sample id array and updated array is:");
        $diff = array_diff($res_ids, $this->user_idnumbers);
        foreach($diff as $d){
            echo $d." ";
        }
        
        foreach($this->user_idnumbers as $sample){
            mtrace(sprintf("asserting that sample id %s is in the list returned from the so-called ready_url",$sample));
            $this->assertTrue(in_array($sample, $res_ids));
        }
        
        
       mtrace("\n--\n");
        
    }
    

}

?>
