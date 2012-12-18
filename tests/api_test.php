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
        $this->user_idnumbers = array(
            890000000,
            890000001,
            890000009,
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
        
        $this->assertEquals($update, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update');
        $this->assertEquals($webservice, 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s?skip_place_holder=true');
        $this->assertEquals($ready, 'https://tt.lsu.edu/api/v2/json/kZabUZ6TZLcsYsCnV6KW/photos/recently_updated/%s');
        
        mtrace("\n--\n");
    }
    
    
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
            $this->assertGreaterThan(0, filesize($fullpath), sprintf("filesize is zero for path %s", $fullpath));
        }
        mtrace("\n--\n");
        return true;
        
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
    public function testGetJsonFromUpdateUrl(){
        global $CFG;
        $this->resetAfterTest(true);

        $idnumber = 890003719;//genLsuId();
        mtrace(sprintf("Getting single image for user with ID %s\n", $idnumber));
        
        $url = get_config('block_my_picture', 'update_url');

        $hash = hash("sha256", $idnumber);
        $curl = new curl();
        $json = $curl->post(sprintf($url, $hash));

        $obj = json_decode($json);

        mtrace(sprintf("request url: %s\n", sprintf($url, $hash)));
        
//        mtrace("\nRAW JSON:\n");
//        mtrace(print_r($json));
        
//        mtrace("\nJSON decoded:\n");
//        mtrace(print_r($obj));
        
        $this->assertTrue(is_object($obj), sprintf("expected an object, got something else...\n"));
        $this->assertNotEmpty($obj);
        $this->assertEquals(1, $obj->success->status);
        $this->assertEquals('Photo update scheduled', $obj->success->message);
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
    public function testListPhotosRecentlyUpdatedNaturally(){

        global $CFG;
        $this->resetAfterTest(true);
        
        
        $time = time() - 3600*24*7;
        mtrace(sprintf("Getting a list of photos updated since %s...", strftime('%F %T', $time)));
        
        $result = mypic_get_users_updated_pictures($time);
        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
        
//        mtrace(print_r($result));
        mtrace(sprintf("got %s users\n", count($result)));
        foreach($result as $user){
            echo $user->idnumber." ";
        }
        mtrace("\n--\n");
        return($result);
    }
    

    /**
     * @depends testGetImageFromWebserviceUrl
     * @global type $CFG
     * @return type
     */
    public function testListPhotosRecentlyUpdatedByUs($updated){

        global $CFG;
        $this->resetAfterTest(true);
        
        if($updated){

            $time = time() - 120;
            mtrace(sprintf("Getting a list of photos updated updated BY THIS TEST SUITE at %s; expect  %s...\n", strftime('%F %T', $time), count($this->user_idnumbers)));
            $result = mypic_get_users_updated_pictures($time);
            $this->assertTrue(is_array($result));
            $this->assertNotEmpty($result);
            
        
            $updated_ids = array();
            if($result){
                foreach($result as $user){
                    $updated_ids[] = $user->idnumber;
                }
            }
            foreach($this->user_idnumbers as $preset){
                if(in_array($preset, $updated_ids)){
                    unset($preset);
                }
            }
            
            foreach($result as $user){
             
                echo $user->idnumber." ";
                
            }
            $this->assertCount(0,$this->user_idnumbers);
    //        mtrace(print_r($result));
            mtrace("\n--\n");
            
        }
    }
    
    
//    /**
//     * @depends testListPhotosRecentlyUpdatedNaturally
//     */
//    public function testDownloadRecentlyUpdatedPhotos($result){
//        global $CFG;
//        $this->resetAfterTest();
//        mtrace("Testing image download for each of those users retrieved as RECENTLY UPDATED...\n");
//        foreach($result as $user_photo_record){
//            $idnumber = $user_photo_record->idnumber;
//
//            $hash = hash("sha256", $idnumber);
//
//            $filename = $idnumber . '.jpg';
//            $fullpath = $CFG->dataroot . '/temp/' . $filename;
//            $fp = fopen($fullpath, 'w');
//
//            $curl = new curl();
//
//            $url = sprintf(get_config('block_my_picture', 'webservice_url'), $hash);
//            $curl->download(array(array('url' => $url, 'file' => $fp)));
//            fclose($fp);
//            
//            mtrace(sprintf("%s - %s\n", $idnumber, $url));
//
//            $this->assertGreaterThan(0, filesize($fullpath), sprintf("filesize is zero for path %s", $fullpath));
//        }
//        mtrace("\n--\n");
//        
//    }
    
}

?>
