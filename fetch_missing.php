<?php

// Author: Adam Zapletal

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('lib.php');
ini_set('max_execution_time','7200');

require_login();

$_s = function($key) { return get_string($key, 'block_my_picture'); };

if (!is_siteadmin($USER->id)) {
    print_error('need_permission', 'block_mypic');
}

$header = $_s('fetch_missing_title');
$pluginname = $_s('pluginname');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/my_picture/fetch_missing.php');
$PAGE->navbar->add($header);
$PAGE->set_title($pluginname . ': ' . $header);
$PAGE->set_heading($SITE->shortname . ': ' . $pluginname);

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

echo '<div>';

echo $_s('fetching_start') . '<br />';

$limit = get_config('block_my_picture', 'cron_users');

$users = mypic_get_users_without_pictures($limit);

if ($users) {
    $force_update = false;
    mypic_batch_update($users, $force_update, '<br />');
} else {
    echo $_s('no_missing_pictures') . '<br />';
}

echo '</div>';

echo $OUTPUT->footer();
