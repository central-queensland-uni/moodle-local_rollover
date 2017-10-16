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

use local_rollover\admin\rollover_settings;
use local_rollover\backup\restore_worker;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_backup_restore_worker_test extends rollover_testcase {
    public function test_restore() {
        self::resetAfterTest(true);

        $destinationcourse = $this->generator()->create_course_by_shortname('destination-course');

        $this->extract_fixture_backup_data();

        $restoreworker = new restore_worker($destinationcourse->id);
        $restoreworker->restore('6810b32987b568760f55d626dcc5448a');

        course_modinfo::clear_instance_cache($destinationcourse);
        $info = get_fast_modinfo($destinationcourse);
        $cm = array_values($info->get_cms())[0];
        self::assertSame('Rollover Assignment', $cm->name);
    }

    public function test_it_cleans_temp_files() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }

    private function extract_fixture_backup_data() {
        global $CFG;

        $zip = new ZipArchive();
        $zip->open(__DIR__ . '/../fixtures/6810b32987b568760f55d626dcc5448a.zip');
        $zip->extractTo($CFG->tempdir . '/backup/');
        $zip->close();

        self::assertFileExists($CFG->tempdir . '/backup/6810b32987b568760f55d626dcc5448a');
    }
}
