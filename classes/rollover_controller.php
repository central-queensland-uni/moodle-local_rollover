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
use local_rollover\form\form_original_course;
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

    /** @var stdClass */
    private $destinationcourse;

    public function __construct(moodle_page $page, $output) {
        $this->page = $page;
        $this->output = $output;
        $this->destinationcourse = get_course(required_param('into', PARAM_INT));
    }

    public function rollover_source_selection_page() {
        require_login($this->destinationcourse);
        $this->page->set_context(context_course::instance($this->destinationcourse->id));
        $this->page->set_url('/local/rollover/index.php', ['into' => $this->destinationcourse->id]);
        $this->page->set_heading($this->destinationcourse->fullname);

        $form = new form_original_course();

        echo $this->output->header();

        if ($form->is_submitted()) {
            $data = $form->get_data();

            global $DB;
            $sourceid = $DB->get_field('course', 'id', ['shortname' => $data->sourceshortname], MUST_EXIST);
            $worker = new rollover_worker($sourceid, $this->destinationcourse->id);
            $worker->rollover();

            echo $this->output->heading(get_string('rolloversuccessful', 'local_rollover'));
            echo get_string('rolloversuccessfulmessage', 'local_rollover', [
                'from' => htmlentities($data->sourceshortname),
                'into' => htmlentities($this->destinationcourse->shortname),
            ]);
            echo '<br /><br />';

            $url = new moodle_url('/course/view.php', ['id' => $this->destinationcourse->id]);
            echo $this->output->single_button($url, get_string('proceed', 'local_rollover'), 'get');
        } else {
            $form->set_data(['into' => $this->destinationcourse->id]);
            echo $this->output->heading(get_string('pluginname', 'local_rollover'));
            $form->display();
        }

        echo $this->output->footer();
    }
}
