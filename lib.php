<?php

require_once($CFG->libdir . '/gdlib.php');

function mypic_get_users_without_pictures() {
    global $DB;

    $params = array('picture' => 0, 'deleted' => 0);

    return $DB->get_records('user', $params);
}

function mypic_insert_picture($userid, $picture_path) {
    global $DB;

    $context = get_context_instance(CONTEXT_USER, $userid);

    if (process_new_icon($context, 'user', 'icon', 0, $picture_path)) {
        return $DB->set_field('user', 'picture', 1, array('id' => $userid));
    }

    return false;
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

function mypic_fetch_picture($idnumber) {
    global $CFG;

    $url = 'https://moodleftp.lsu.edu/index.php';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'idnumber=' . $idnumber);

    $filename = $idnumber . '.jpg';
    $fullpath = $CFG->dataroot . '/temp/' . $filename;

    $fp = fopen($fullpath, 'w');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_exec($ch);

    curl_close($ch);
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
// 1 - Bad idnumber, contact moodle admin picture inserted
// 2 - Success, tiger card picture inserted
// 3 - Picture not found, visit tiger card office picture inserted
function mypic_update_picture($user) {
    if (!mypic_is_lsuid($user->idnumber)) {
        return (int) mypic_insert_badid($user->id);
    }

    if ($path = mypic_fetch_picture($user->idnumber)) {
        return (int) mypic_insert_picture($user->id, $path) * 2;
    }

    return (int) mypic_insert_nopic($user->id) * 3;
}

function mypic_batch_update($users, $sep='', $step=100) {
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

        $$result_map[mypic_update_picture($user)]++;

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
