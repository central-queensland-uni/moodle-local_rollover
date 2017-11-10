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

use local_rollover\local\backup_history;
use local_rollover\task\backup_history_cleaner_task;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_task_history_cleaner_task_test extends rollover_testcase {
    /** @var string */
    private $filename = null;

    private function prepare_setting_and_file($durationsetting, $fileage, $extension = 'mbz', $filename = null) {
        self::resetAfterTest();

        set_config(backup_history::SETTING_BACKUP_DURATION, $durationsetting, 'local_rollover');

        if (empty($filename)) {
            $filetime = time() - $fileage;
            $filename = date('Y-m-d_H-i-s', $filetime) . "_my-course.{$extension}";
        }
        $filename = backup_history::get_setting_location($filename);
        mkdir(backup_history::get_setting_location(), 0777, true);
        touch($filename);

        $this->filename = $filename;
    }

    public function test_it_has_a_name() {
        $task = new backup_history_cleaner_task();
        $name = $task->get_name();
        self::assertContains('history', $name);
    }

    public function test_it_cleans_old_files() {
        $this->prepare_setting_and_file(DAYSECS, 2 * DAYSECS);

        $task = new backup_history_cleaner_task();
        $task->execute();

        self::assertFileNotExists($this->filename);
    }

    public function test_it_does_not_clean_newer_files() {
        $this->prepare_setting_and_file(DAYSECS, 2 * HOURMINS);

        $task = new backup_history_cleaner_task();
        $task->execute();

        self::assertFileExists($this->filename);
    }

    public function test_it_does_not_clean_if_date_format_not_valid() {
        $this->prepare_setting_and_file(DAYSECS, null, null, 'something.mbz');

        $task = new backup_history_cleaner_task();
        $task->execute();

        self::assertFileExists($this->filename);
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

    public function provider_for_test_it_breaksdown_filename() {
        date_default_timezone_set('UTC');

        $now = time();
        $nowdate = date('Y-m-d_H-i-s', $now);

        return [
            ['', null],
            [$nowdate . '_course.txt', null],
            [$nowdate . '.mbz', null],
            ['helloworld.mbz', null],
            [$nowdate . '_course.mbz', $now],
        ];
    }

    /**
     * @dataProvider provider_for_test_it_breaksdown_filename
     */
    public function test_it_breaksdown_filename($filename, $expected) {
        date_default_timezone_set('UTC');

        $actual = backup_history_cleaner_task::get_filename_timestamp($filename);
        self::assertSame($expected, $actual, "Filename: {$filename}");
    }
}
