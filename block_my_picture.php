<?php

// Author: Adam Zapletal

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/blocks/my_picture/lib.php');

class block_my_picture extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_my_picture');
    }
    
    function has_config(){
        return true;
    }
    
    public function applicable_formats() {
        return array(
            'site' => true,
            'my' => true,
            'site-index' => true,
            'course-view' => true, 
        );
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

        if (get_config('block_my_picture', 'fetch')) {
            $limit = get_config('block_my_picture', 'cron_users');
            $users = mypic_get_users_without_pictures($limit);
        } else {
            $params = array('name' => 'my_picture');

            $cron = $DB->get_field('block', 'cron', $params);
            $lastcron = $DB->get_field('block', 'lastcron', $params);

            // Chosen time would either be cron time, or the last run time
            $start_time = min(time() - $cron, $lastcron);

            $users = mypic_get_users_updated_pictures($start_time);
        }

        if (!$users) {
            echo $_s('no_missing_pictures') . '<br />';
        } else {
            mypic_batch_update($users);
        }

        return true;
    }
}
