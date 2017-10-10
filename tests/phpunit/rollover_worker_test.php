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

use local_rollover\rollover_worker;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_controller_test extends rollover_testcase {
    public function test_backup() {
        self::resetAfterTest(true);

        $sourcecourse = $this->generator()->create_course_by_shortname('backup-source-course');
        $this->generator()->create_assignment('backup-source-course', 'Backup Assignment');

        $worker = new rollover_worker($sourcecourse->id, 0);
        $worker->backup();

        self::assertFileExists($worker->get_backup_path());
        $xml = file_get_contents($worker->get_backup_path() . '/moodle_backup.xml');
        self::assertContains('<original_course_shortname>backup-source-course</original_course_shortname>', $xml);
        self::assertContains('<title>Backup Assignment</title>', $xml);
    }

    public function test_restore() {
        self::resetAfterTest(true);

        $destinationcourse = $this->generator()->create_course_by_shortname('destination-course');

        $worker = new rollover_worker(0, $destinationcourse->id);
        $worker->set_backup_id('6810b32987b568760f55d626dcc5448a');

        $this->extract_fixture_backup_data();
        $worker->restore();

        course_modinfo::clear_instance_cache($destinationcourse);
        $info = get_fast_modinfo($destinationcourse);
        $cm = array_values($info->get_cms())[0];
        self::assertSame('Rollover Assignment', $cm->name);
    }

    public function test_rollover() {
        self::resetAfterTest(true);

        $sourcecourse = $this->generator()->create_course_by_shortname('rollover-from');
        $destinationcourse = $this->generator()->create_course_by_shortname('rollover-into');
        $this->generator()->create_assignment('rollover-from', 'Full Rollover Assignment');

        $worker = new rollover_worker($sourcecourse->id, $destinationcourse->id);
        $worker->rollover();

        course_modinfo::clear_instance_cache($destinationcourse);
        $info = get_fast_modinfo($destinationcourse);
        $cm = array_values($info->get_cms())[0];
        self::assertSame('Full Rollover Assignment', $cm->name);
    }

    public function test_it_includes_the_log_in_the_backup() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }

    public function test_it_cleans_temp_files() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }

    private function extract_fixture_backup_data() {
        global $CFG;

        $zip = new ZipArchive();
        $zip->open(__DIR__ . '/fixtures/6810b32987b568760f55d626dcc5448a.zip');
        $zip->extractTo($CFG->tempdir . '/backup/');
        $zip->close();

        self::assertFileExists($CFG->tempdir . '/backup/6810b32987b568760f55d626dcc5448a');
    }

    public function test_it_runs_as_admin() {
        // Is it really needed to run as user 2 (admin)?
        // Let's figure out when we the specific capabilities.
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }
}
