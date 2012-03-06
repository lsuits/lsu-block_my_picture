<?php

// Author: Adam Zapletal

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/blocks/my_picture/lib.php');

class block_my_picture extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_my_picture');
    }

    function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        global $CFG, $OUTPUT;

        $this->content = new stdClass;
        $this->content->icons = array();
        $this->content->items = array();
        $this->content->footer = '';

        $reprocess_str = get_string('reprocess', 'block_my_picture');

        $reprocess_help = $OUTPUT->help_icon('pluginname', 'block_my_picture');

        $reprocess_link = '
            <a href = "' . $CFG->wwwroot . '/blocks/my_picture/reprocess.php"
               alt = "'. $reprocess_str . '">' . $reprocess_str . '</a> ' .
               $reprocess_help;

        $this->content->items[] = $reprocess_link;

        return $this->content;
    }

    function cron() {
        global $CFG, $DB;

        $_s = function($k, $a=null) {
            return get_string($k, 'block_my_picture', $a);
        };

        mtrace("\n" . $_s('start'));

        $cron_num_users = $CFG->block_my_picture_cron_users;

        mtrace($_s('fetched', $cron_num_users));

        $users = mypic_get_users_without_pictures($cron_num_users);

        if (!$users) {
            echo $_s('no_missing_pictures') . '<br />';
        } else {
            mypic_batch_update($users);
        }
        
        return true;
    }
}
