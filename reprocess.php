<?php

// Author: Adam Zapletal

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('lib.php');

require_login();

$_s = function($key) { return get_string($key, 'block_my_picture'); };

$header = $_s('reprocess_title');
$pluginname = $_s('pluginname');

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/blocks/my_picture/reprocess.php');
$PAGE->navbar->add($header);
$PAGE->set_title($pluginname . ': ' . $header);
$PAGE->set_heading($SITE->shortname . ': ' . $pluginname);

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

$result_map = array(
    0 => 'error_user',
    1 => 'badid_user',
    2 => 'success_user',
    3 => 'nopic_user'
);

echo '<div>' . $_s($result_map[mypic_update_picture($USER)]) . '</div>';

echo $OUTPUT->footer();
