<?php
global $CFG;
defined('MOODLE_INTERNAL') || die;
require_once 'lib.php';
$_s = function($key) { return get_string($key, 'block_my_picture'); };

if ($ADMIN->fulltree) {

    $default_url = $CFG->wwwroot;
    $settings->add(new admin_setting_configtext('block_my_picture/webservice_url',
        $_s('webservice_url'), $_s('url'), $default_url));

    $settings->add(new admin_setting_configtext('block_my_picture/ready_url',
        $_s('ready_url'), $_s('url'), $default_url));

//    $settings->add(new admin_setting_configtext('block_my_picture/update_url',
//        $_s('update_url'), $_s('url'), $default_url));

    $settings->add(new admin_setting_configcheckbox('block_my_picture/fetch',
        $_s('fetch'), $_s('fetch_desc'), 1));

    $settings->add(new admin_setting_configtext('block_my_picture/cron_users',
        $_s('cron_users'), $_s('cron_users_desc'), 100));

    $reprocess_all_link = '<a href = "' . $CFG->wwwroot . '/blocks/my_picture/reprocess_all.php">' . $_s('reprocess_all') . '</a>';
    $fetch_missing_link = '<a href = "' . $CFG->wwwroot . '/blocks/my_picture/fetch_missing.php">' . $_s('fetch_missing') . '</a>';

    $settings->add(new admin_setting_heading('block_mypicture_reprocess_all_heading', '', $reprocess_all_link));
    $settings->add(new admin_setting_heading('block_mypicture_fetch_missing_heading', '', $fetch_missing_link));
}
