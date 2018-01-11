<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Connects to LSU web service for downloading and updating user photos
 *
 * @package    block_my_picture
 * @copyright  2008, Adam Zapletal, 2017, Robert Russo, Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Set and get the config variable
global $CFG;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/blocks/my_picture/lib.php');

$_s = function($key) { return get_string($key, 'block_my_picture'); };

if ($ADMIN->fulltree) {

    $default_url = $CFG->wwwroot;
    $settings->add(new admin_setting_configtext('block_my_picture/webservice_url',
        $_s('webservice_url'), $_s('url'), $default_url));

    $settings->add(new admin_setting_configtext('block_my_picture/ready_url',
        $_s('ready_url'), $_s('url'), $default_url));

    $settings->add(new admin_setting_configcheckbox('block_my_picture/fetch',
        $_s('fetch'), $_s('fetch_desc'), 1));

    $settings->add(new admin_setting_configtext('block_my_picture/cron_users',
        $_s('cron_users'), $_s('cron_users_desc'), 100));

    $reprocess_all_link = '<a href = "' . $CFG->wwwroot . '/blocks/my_picture/reprocess_all.php">' . $_s('reprocess_all') . '</a>';
    $fetch_missing_link = '<a href = "' . $CFG->wwwroot . '/blocks/my_picture/fetch_missing.php">' . $_s('fetch_missing') . '</a>';

    $settings->add(new admin_setting_heading('block_mypicture_reprocess_all_heading', '', $reprocess_all_link));
    $settings->add(new admin_setting_heading('block_mypicture_fetch_missing_heading', '', $fetch_missing_link));
}
