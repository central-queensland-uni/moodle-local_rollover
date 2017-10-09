<?php
// This file is part of Moodle Course Rollover Plugin
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
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rollover;

use context_course;
use local_rollover\form\form_source_course_selection;
use moodle_page;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rollover_controller {
    /** @var moodle_page */
    private $page;

    /** @var stdClass */
    private $output;

    public function set_output($output) {
        $this->output = $output;
    }

    /** @var stdClass */
    private $destinationcourse;

    public function __construct() {
        global $OUTPUT, $PAGE;

        $this->page = $PAGE;
        $this->output = $OUTPUT;

        $this->destinationcourse = get_course(required_param('into', PARAM_INT));
    }

    public function index() {
        require_login($this->destinationcourse);
        $this->page->set_context(context_course::instance($this->destinationcourse->id));
        $this->page->set_url('/local/rollover/index.php', ['into' => $this->destinationcourse->id]);
        $this->page->set_heading($this->destinationcourse->fullname);

        if (!is_null(optional_param('sourceshortname', null, PARAM_TEXT))) {
            $this->source_selection_page_next();
        } else {
            $this->source_selection_page();
        }
    }

    public function source_selection_page() {
        $form = $this->create_form_source_course_selection();

        echo $this->output->header();

        $form->set_data(['into' => $this->destinationcourse->id]);
        echo $this->output->heading(get_string('pluginname', 'local_rollover'));
        $form->display();

        echo $this->output->footer();
    }

    public function source_selection_page_next() {
        $form = $this->create_form_source_course_selection();

        if (!$form->is_submitted()) {
            $this->source_selection_page();
            return;
        }

        $data = $form->get_data();

        global $DB;
        $sourceid = $DB->get_field('course', 'id', ['shortname' => $data->sourceshortname], MUST_EXIST);
        $worker = new rollover_worker($sourceid, $this->destinationcourse->id);
        $worker->rollover();

        $this->rollover_complete($data->sourceshortname);
    }

    /**
     * @return form_source_course_selection
     */
    public function create_form_source_course_selection() {
        global $DB;

        $courses = get_user_capability_course('moodle/course:update');
        if ($courses === false) {
            $courses = [];
        }

        foreach ($courses as &$course) {
            $course = $course->id;
        }

        $courses = $DB->get_records_list('course',
                                         'id',
                                         $courses,
                                         'shortname ASC',
                                         'id,shortname,fullname');
        unset($courses[$this->destinationcourse->id]);

        return new form_source_course_selection($courses);
    }

    public function rollover_complete($sourceshortname) {
        echo $this->output->header();

        echo $this->output->heading(get_string('rolloversuccessful', 'local_rollover'));
        echo get_string('rolloversuccessfulmessage', 'local_rollover', [
            'from' => htmlentities($sourceshortname),
            'into' => htmlentities($this->destinationcourse->shortname),
        ]);
        echo '<br /><br />';

        $url = new moodle_url('/course/view.php', ['id' => $this->destinationcourse->id]);
        echo $this->output->single_button($url, get_string('proceed', 'local_rollover'), 'get');

        echo $this->output->footer();
    }
}
