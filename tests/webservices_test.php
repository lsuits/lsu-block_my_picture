<?php

require_once dirname(dirname(__FILE__)).'/lib.php';

/**
 * implementing classes will provide local values through these methods
 */
interface photoWebservice {
    public function webservice_url();
    public function ready_url();
    public function update_url();
    public function info_url();
    
    public function getMoodleUserDetailsForKnownUser();
    public function getWebserviceJsonDetailsForKnownUser();
    public function getValidUserIds();
    public function getIdnumberWithoutPicture();
}


/**
 * exercises the webservice endpoints directly
 * 
 * provides helper functions for subclasses
 */
class mypic_webservices_testcase extends advanced_testcase {

    public $ws;
    public $knownMoodleUser;
    
    public function setup(){
        $this->resetAfterTest();
        $this->initWebserviceConfigValues();
    }
    
    /**
     * iniitalize config with local webservices URLs
     */
    private function initWebserviceConfigValues(){
        require_once('webservices.php');
        
        $this->ws = new TigerTracker();
        set_config('webservice_url', $this->ws->webservice_url(), 'block_my_picture');
        set_config('ready_url', $this->ws->ready_url(), 'block_my_picture');
        set_config('update_url', $this->ws->update_url(), 'block_my_picture');
        set_config('info_url', $this->ws->info_url(), 'block_my_picture');
    }
    
    //helper function building WS URL for given user
    public function buildUrlByIdnumber($base,$idnumber){
        $hash = hash('sha256', $idnumber);
        return sprintf($base, $hash);
    }

    //makes a WS request using the param $url
    public function fetchFromWebserviceByIdnumber($url){
        $curl = new curl();
        return $curl->get($url);
    }
    
    //fetch image for the given user directly from the WS
    public function downloadFfromWebserviceByIdnumber($base,$idnumber){
        global $CFG;
        
        $url  = $this->buildUrlByIdnumber($base, $idnumber);
        $path = $CFG->dataroot . '/temp/' . $idnumber . '.jpg';;
        $curl = new curl();
        $file = fopen($path, 'w');
        $curl->download(array(array('url' => $url, 'file' => $file)));
        fclose($file);

        return $path;
    }

    protected function insertKnownUserIntoMoodle(){
        return $this->knownMoodleUser = $this->generateUser(
                $this->ws->getMoodleUserDetailsForKnownUser()
            );
    }
    
    protected function generateUser($params = array()){
        return $this->getDataGenerator()->create_user($params);
    }

    /**
     * ensure that the webservice response matches known values
     * for a known user
     */
    public function testInfoUrlForKnownUser(){
        $this->insertKnownUserIntoMoodle();

        $serviceUrl = $this->buildUrlByIdnumber(
                $this->ws->info_url(),
                $this->knownMoodleUser->idnumber
                );
        
        $webserviceresponse = $this->fetchFromWebserviceByIdnumber($serviceUrl);
            $this->assertJsonStringEqualsJsonString(
                $this->ws->getWebserviceJsonDetailsForKnownUser(), 
                $webserviceresponse
                );
    }

    //ensure that image downloaded for a known user is identical to the test suite image
    public function testWebserviceUrlForKnownUser(){
        $this->insertKnownUserIntoMoodle();

        $path = $this->downloadFfromWebserviceByIdnumber(
                $this->ws->webservice_url(),
                $this->knownMoodleUser->idnumber
                );
        $this->assertInternalType('string',$path);
        $this->assertFileExists($path, sprintf("Couldn't find file %s", $path));
        $this->assertFileEquals('tests/mike.jpg', $path);
        $size = filesize($path);
        $this->assertGreaterThan(1, $size);
    }
}

?>
