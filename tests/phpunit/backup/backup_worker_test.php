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
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_backup_backup_worker_test extends rollover_testcase {
    public function test_backup() {
        global $DB;
        self::resetAfterTest(true);

        $sourcecourse = $this->generator()->create_course_by_shortname('backup-source-course');
        $this->generator()->create_assignment('backup-source-course', 'Backup Assignment');

        $backupworker = backup_worker::create($sourcecourse->id);
        $backupworker->backup();

        $dbfiles = $DB->count_records('files', ['filename' => $backupworker->get_db_filename()]);
        self::assertSame(0, $dbfiles, 'It should not leave files in Moodle.');
        self::assertFileExists($backupworker->get_path());
        $xml = file_get_contents($backupworker->get_path() . '/moodle_backup.xml');
        self::assertContains('<original_course_shortname>backup-source-course</original_course_shortname>', $xml);
        self::assertContains('<title>Backup Assignment</title>', $xml);
    }

    public function test_it_includes_the_log_in_the_backup() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }

    public function test_it_creates_given_a_source_course_id() {
        self::resetAfterTest(true);
        $sourcecourse = $this->generator()->create_course_by_shortname('backup-source-course');

        $backupworker = backup_worker::create($sourcecourse->id);
        self::assertNotNull($backupworker);
        self::assertNotEmpty($backupworker->get_backup_id());
        self::assertEquals($sourcecourse->id, $backupworker->get_source_course_id());
    }

    public function test_it_loads_given_a_backup_id() {
        self::resetAfterTest(true);
        $sourcecourse = $this->generator()->create_course_by_shortname('backup-source-course');

        $backupworker = backup_worker::create($sourcecourse->id);
        $backupid = $backupworker->get_backup_id();
        $backupworker->save();

        $backupworker = backup_worker::load($backupid);
        self::assertNotNull($backupworker);
        self::assertNotEmpty($backupworker->get_backup_id());
        self::assertEquals($sourcecourse->id, $backupworker->get_source_course_id());
    }
}
