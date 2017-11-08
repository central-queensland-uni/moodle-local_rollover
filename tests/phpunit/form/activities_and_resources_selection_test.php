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

use local_rollover\backup\backup_worker;
use local_rollover\form\steps\form_activities_and_resources_selection;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

class local_rollover_form_activities_and_resources_selection_test extends rollover_testcase {
    public function test_it_creates() {
        parent::setUp();
        $this->resetAfterTest();

        $source = $this->generator()->create_course_by_shortname('source');
        $this->generator()->create_activity('source', 'assignment', 'my test assignment');

        $worker = backup_worker::create($source->id);
        $tasks = $worker->get_backup_tasks();
        $form = new form_activities_and_resources_selection($tasks);

        ob_start();
        $form->display();
        $html = ob_get_clean();

        self::assertContains('my test assignment', $html);
    }
}
