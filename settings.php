<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $_s = function($key) { return get_string($key, 'block_my_picture'); };

    $settings->add(new admin_setting_configtext('block_my_picture_cron_users', $_s('cron_users_desc'), $_s('cron_users'), 100, PARAM_INT));

    $reprocess_all_link = '<a href = "' . $CFG->wwwroot . '/blocks/my_picture/reprocess_all.php">' . $_s('reprocess_all') . '</a>';
    $fetch_missing_link = '<a href = "' . $CFG->wwwroot . '/blocks/my_picture/fetch_missing.php">' . $_s('fetch_missing') . '</a>';

    $settings->add(new admin_setting_heading('block_mypicture_reprocess_all_heading', '', $reprocess_all_link));
    $settings->add(new admin_setting_heading('block_mypicture_fetch_missing_heading', '', $fetch_missing_link));
}
