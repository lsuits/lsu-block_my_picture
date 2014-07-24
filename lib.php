<?php
global $CFG;

require_once($CFG->libdir . '/gdlib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * 
 * @global stdClass $DB
 * @param int $limit how many to fetch
 * @return stdClass[]
 */
function mypic_get_users_without_pictures($limit=0) {
    global $DB;
    $select = "picture <> 1 AND deleted = 0 AND suspended = 0 ORDER BY RAND() LIMIT " . $limit;
    return $DB->get_records_select('user', $select);
}

/**
 * NB: Using the config 'ready_url', this corresponds to API docs for 'recently_updated'
 * @param type $start_time how far back to check for users marked as 'updated' 
 * in the external system
 * @return mixed array
 */
function mypic_get_users_updated_pictures($start_time) {

    $start_date = strftime("%Y%m%d%H", $start_time);

    $ready_url = get_config('block_my_picture', 'ready_url');

    $curl = new curl();
    $json = $curl->get(sprintf($ready_url, $start_date));

    $res = json_decode($json);

    $to_moodle = function($user) {
        return $user->id_number;
    };

    $validUsers = mypic_WebserviceIntersectMoodle(array_map($to_moodle, $res->users));

    return empty($res->users) ? array() : $validUsers;
}

/**
 * For a given array of idnumbers, return only the subset that are valid in the database
 * @global type $DB
 * @param int[] $idnumbers array of idnumber keys to fetch users with
 * @return stdClass[] user row objects from the DB
 */
function mypic_WebserviceIntersectMoodle($idnumbers = array()){
    global $DB;
    return array_values($DB->get_records_list('user', 'idnumber', $idnumbers, '','id, firstname, lastname, idnumber'));
}

function mypic_insert_picture($userid, $picture_path) {
    global $DB, $CFG;

    $context = context_user::instance($userid);
    
    $pathparts  = explode('/', $picture_path);
    $file       = array_pop($pathparts);
    $dir        = array_pop($pathparts);
    $shortpath  = $dir.'/'.$file;
    
    if(!file_exists($picture_path)){
//        Removed until we migrate to the new events system.
//        add_to_log(0, 'my_pic', "insert picture",'',sprintf("File %s does not exist for user %s", $shortpath, $userid));
        return false;
    }elseif($picture_path == $CFG->dirroot . '/blocks/my_picture/images/nopic.png'){
//        Removed until we migrate to the new events system.
//        add_to_log(0, 'my_pic', "insert picture",'',sprintf("1-byte file %s for user %s", $shortpath, $userid));
        try{
            process_new_icon($context, 'user', 'icon', 0, $picture_path);
            return $DB->set_field('user', 'picture', 2, array('id' => $userid));
        }catch(Exception $e){
//            Removed until we migrate to the new events system.
//            add_to_log(0, 'my_pic', "insert picture",'',sprintf("Exception '%s' caught while trying to insert nopic.jpg for user %s", $e->message, $userid));
        }
    }else{
        try{
            process_new_icon($context, 'user', 'icon', 0, $picture_path);
            return $DB->set_field('user', 'picture', 1, array('id' => $userid));
        }catch(Exception $e){
//            Removed until we migrate to the new events system.
//            add_to_log(0, 'my_pic', "insert picture",'',sprintf("Exception '%s' caught while trying to insert picture for user %s", $e->message, $userid));
        }
    }
//        Removed by Jason Peak at some earlier time.
//        add_to_log(0, 'my_pic', "insert picture",'',sprintf("Unknown failure for file %s for user %s", $shortpath, $userid));
//        return false;
}

function mypic_insert_nopic($userid) {
    global $CFG;

    $nopic_path = $CFG->dirroot . '/blocks/my_picture/images/nopic.png';

    return mypic_insert_picture($userid, $nopic_path);
}

function mypic_insert_badid($userid) {
    global $CFG;

    $badid_path = $CFG->dirroot . '/blocks/my_picture/images/badid.jpg';

    return mypic_insert_picture($userid, $badid_path);
}

/**
 * This method calls the webservice show() method, requests return as json
 * @param type $idnumber 89-number
 * @param type $hash 
 * @deprecated no longer need update functionality
 * @return boolean
 */
function mypic_force_update_picture($idnumber, $hash = null) {
    throw new coding_exception("There is no longer any need to 'update' photos; please do not call this function");
    
    $url = get_config('block_my_picture', 'update_url');

    if (empty($url)) {
        return true;
    }

    if (empty($hash)) {
        $hash = hash("sha256", $idnumber);
    }

    $curl = new curl();
    $json = $curl->post(sprintf($url, $hash));

    $obj = json_decode($json);

    return (
        isset($obj->success) and
        $obj->success->message == 'Photo update scheduled' and
        $obj->success->status == 1
    );
}

/**
 * This method calls webservice show() method requesting response as jpg
 * @global type $CFG
 * @param type $idnumber 89-number
 * @param type $updating trigger the external service to mark the user photo as updated
 * @return boolean|string
 */
function mypic_fetch_picture($idnumber, $updating = false) {
    global $CFG;

    $hash = hash("sha256", $idnumber);
    $name = $idnumber . '.jpg';
    $path = $CFG->dataroot . '/temp/' . $name;
    $url  = sprintf(get_config('block_my_picture', 'webservice_url'), $hash);
    $curl = new curl();
    $file = fopen($path, 'w');
    $curl->download(array(array('url' => $url, 'file' => $file)));
    fclose($file);

    if(!empty($curl->response['Status']) and $curl->response['Status'] == '404'){
//        Removed until we migrate to the new events system.
//        add_to_log(0, 'my_pic', "insert picture",'',sprintf("404 for user %s", $idnumber));
        mtrace(sprintf("404 for user %s", $idnumber));
        unlink($path);
        return false;
    }

    return $path;
}

function mypic_is_lsuid($idnumber) {
    return preg_match('/^89\d{7}$/', $idnumber);
}

// Return values:
// 0 - Error
// 1 - Bad idnumber, 'contact moodle admin' picture inserted
// 2 - Success, tiger card office picture inserted
// 3 - Picture not found, 'visit tiger card office' picture inserted
function mypic_update_picture($user, $updating=false) {

    if (!mypic_is_lsuid($user->idnumber)) {
        $u  = isset($user->username) ? $user->username : '<not set>';
        $id = isset($user->idnumber) ? $user->idnumber : '<not set>';

//        Removed until we migrate to the new events system.
//        add_to_log(0, 'my_pic', "update picture", '', sprintf("bad id %s for user %s", $id, $u));

        return (int) mypic_insert_badid($user->id);
    }

    if ($path = mypic_fetch_picture($user->idnumber, $updating)) {
        return (int) mypic_insert_picture($user->id, $path) * 2;
    }

    return (int) mypic_insert_nopic($user->id) * 3;
    return 0;
}

function mypic_batch_update($users, $updating=false, $sep='', $step=100) {
    $_s = function($k, $a=null) {
        return get_string($k, 'block_my_picture', $a);
    };

    $start_time = microtime();

    $count = $num_success = $num_error = $num_nopic = $num_badid = 0;

    foreach ($users as $user) {
        mtrace('Processing image for (' . $user->idnumber . ') ');

        // Keys are error codes, values are counter variables to increment
        $result_map = array(
            0 => 'num_error',
            1 => 'num_badid',
            2 => 'num_success',
            3 => 'num_nopic'
        );

        $mypicreturncode = mypic_update_picture($user, $updating);

        mtrace($result_map[$mypicreturncode] . $sep);

        $$result_map[$mypicreturncode]++;

        $count++;

        if (!($count % $step)) {
            mtrace($_s('completed', $count) . $sep);
        }
    }

    $time_diff = round(microtime_diff($start_time, microtime()), 1);

    mtrace($_s('finish', $count) . $sep);

    foreach (array('success', 'nopic', 'error', 'badid') as $report) {
        $num = ${'num_' . $report};

        $percent = round($num / $count * 100, 2);
        $str = $_s('num_' . $report);

        mtrace("$num ($percent%) $str $sep");
    }

    mtrace($_s('elapsed', $time_diff) . $sep);
    return array(
        'count'     => $count,
        'success'   => $num_success,
        'error'     => $num_error,
        'nopic'     => $num_nopic,
        'badid'     => $num_badid
        );
}

function mypic_verifyWebserviceExists(){
    $ready = get_config('block_my_picture', 'ready_url');
    $curl  = new curl();
    $json  = $curl->get(sprintf($ready));

    if(is_null((json_decode($json)))){
        mypic_emailAdminsFailureMsg($ready);
        return false;
    }
    return true;
}

/**
 * Simple email routine for messaging to admins
 * @global type $CFG
 * @global stdClass $DB
 * @global type $USER
 * @return int number of errors encountered while sending email
 */
function mypic_emailAdminsFailureMsg($address='<none given>'){
    global $CFG, $DB, $USER;
    
    $subject = get_string('misconfigured_subject', 'block_my_picture');
    $message = get_string('misconfigured_message', 'block_my_picture', $address);
    
    mtrace(sprintf('addr arg = %s, message = %s', $address, $message));

    $adminIds     = explode(',',$CFG->siteadmins);
    $admins = $DB->get_records_list('user', 'id',$adminIds);
    $errors = 0;
    foreach($admins as $admin){
        $success = email_to_user(
                $admin,                 // to
                $USER,                  // from
                $subject,               // subj
                $message,               // body in plain text
                $message,               // body in HTML
                '',                     // attachment
                '',                     // attachment name
                true,                   // user true address ($USER)
                $CFG->noreplyaddress,   // reply-to address
                get_string('pluginname', 'block_my_picture') // reply-to name
            );
        if(!$success){
            $errors++;
        }
    }
    return $errors == 0 ? true : false;
}
