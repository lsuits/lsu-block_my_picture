<?php

// Author: Adam Zapletal

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('lib.php');

require_login();

$_s = function($key) { return get_string($key, 'block_my_picture'); };

ini_set('max_execution_time','36000');

if (!is_siteadmin($USER->id)) {
    error('need_permission', 'block_my_picture');
}

$header = $_s('reprocess_all_title');
$pluginname = $_s('pluginname');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/my_picture/reprocess_all.php');
$PAGE->navbar->add($header);
$PAGE->set_title($pluginname . ': ' . $header);
$PAGE->set_heading($SITE->shortname . ': ' . $pluginname);

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

$params = array('deleted' => '0');
$users = $DB->get_records('user', $params, '', 'id, idnumber');

echo '<div>';

echo $_s('all_start') . '<br />';

$force_update = true;
mypic_batch_update($users, $force_update, '<br />');

echo '</div>';

echo $OUTPUT->footer();
