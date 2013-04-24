<?php

require_once($CFG->libdir . '/gdlib.php');
require_once($CFG->libdir . '/filelib.php');

function mypic_get_users_without_pictures($limit=0) {
    global $DB;

    $sql = "SELECT * FROM {user} u WHERE u.picture = 0 AND u.deleted = 0 ORDER BY RAND() LIMIT {$limit}";
    return $DB->get_records_sql($sql);
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
        $user->firstname = $user->first_name;
        $user->lastname = $user->last_name;
        $user->idnumber = $user->id_number;

        unset($user->first_name, $user->last_name, $user->id_number);
        return $user;
    };

    return empty($res->users) ? array() : array_map($to_moodle, $res->users);
}

function mypic_insert_picture($userid, $picture_path) {
    global $DB;

    $context = get_context_instance(CONTEXT_USER, $userid);
    
    $pathparts = explode('/', $picture_path);
    $file = array_pop($pathparts);
    $dir  = array_pop($pathparts);
    $shortpath = $dir.'/'.$file;
    
    if(!file_exists($picture_path)){
        mtrace(sprintf("File %s does not exist", $picture_path));
        add_to_log(0, 'my_pic', "insert picture",'',sprintf("File %s does not exist for user %s", $shortpath, $userid));
        return false;
    }elseif(filesize($picture_path)<=1){
        mtrace(sprintf("File %s exists, but filesize is less than or equal to 1 byte", $picture_path));
        add_to_log(0, 'my_pic', "insert picture",'',sprintf("1-byte file %s for user %s", $shortpath, $userid));
        return false;
    }elseif(process_new_icon($context, 'user', 'icon', 0, $picture_path)) {
        return $DB->set_field('user', 'picture', 1, array('id' => $userid));
    }else{
        add_to_log(0, 'my_pic', "insert picture",'',sprintf("Unknown failure for file %s for user %s", $shortpath, $userid));
        return false;
    }
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
 * @return boolean
 */
function mypic_force_update_picture($idnumber, $hash = null) {
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
 * @param type $updating trigger the external service to mark the user photo as updated?
 * @return boolean|string
 */
function mypic_fetch_picture($idnumber, $updating = false) {
    global $CFG;

    $hash = hash("sha256", $idnumber);

    $filename = $idnumber . '.jpg';
    $fullpath = $CFG->dataroot . '/temp/' . $filename;
    $fp = fopen($fullpath, 'w');

    $curl = new curl();

    // Could not update photo
    if ($updating and !mypic_force_update_picture($idnumber, $hash)) {
        return false;
    }

    $url = sprintf(get_config('block_my_picture', 'webservice_url'), $hash);
    $curl->download(array(array('url' => $url, 'file' => $fp)));

    fclose($fp);

    if (!filesize($fullpath)) {
        unlink($fullpath);
        return false;
    }

    return $fullpath;
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
        
        add_to_log(0, 'my_pic', "update picture",'',sprintf("bad id %s for user %s", $id, $u));
        
        return (int) mypic_insert_badid($user->id);
    }

    if ($path = mypic_fetch_picture($user->idnumber, $updating)) {
        return (int) mypic_insert_picture($user->id, $path) * 2;
    }

    return (int) mypic_insert_nopic($user->id) * 3;
}

function mypic_batch_update($users, $updating=false, $sep='', $step=100) {
    $_s = function($k, $a=null) {
        return get_string($k, 'block_my_picture', $a);
    };

    $start_time = microtime();

    $count = $num_success = $num_error = $num_nopic = $num_badid = 0;

    foreach ($users as $user) {
        mtrace('Processing image for (' . $user->idnumber . ') ' . $sep);

        // Keys are error codes, values are counter variables to increment
        $result_map = array(
            0 => 'num_error',
            1 => 'num_badid',
            2 => 'num_success',
            3 => 'num_nopic'
        );

        $$result_map[mypic_update_picture($user, $updating)]++;

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
}