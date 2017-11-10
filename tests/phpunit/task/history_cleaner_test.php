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

use local_rollover\task\backup_history_cleaner_task;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_task_history_cleaner_test extends rollover_testcase {
    public function test_it_has_a_name() {
        $task = new backup_history_cleaner_task();
        $name = $task->get_name();
        self::assertContains('history', $name);
    }

    public function test_it_runs() {
        $task = new backup_history_cleaner_task();
        $task->execute();
        // No exception should be thrown.
    }

    public function test_it_is_listed_in_the_tasks_file() {
        global $CFG;

        $tasks = [];
        require($CFG->dirroot . '/local/rollover/db/tasks.php');

        $found = false;
        foreach ($tasks as $task) {
            if ($task['classname'] == backup_history_cleaner_task::class) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);
    }
}
