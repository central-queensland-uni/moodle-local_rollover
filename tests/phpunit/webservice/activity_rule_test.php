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

use local_rollover\test\rollover_testcase;
use local_rollover\webservice\activity_rule_webservice;

defined('MOODLE_INTERNAL') || die();

class local_rollover_webservice_activity_tule_test extends rollover_testcase {
    public function setUp() {
        $this->resetAfterTest();
        self::setAdminUser();
        $this->generator()->create_course_by_shortname('Some Course');
        parent::setUp();
    }

    public function test_the_webservice_exists() {
        $services = null;
        require(__DIR__ . '/../../../db/services.php');
        self::assertArrayHasKey('local_rollover_activity_rule', $services);
    }

    public function test_it_returns_the_original_request() {
        $args = ['moduleid' => 1, 'regex' => '/^Test .*$/'];
        $response = $this->call_webservice_successfully(activity_rule_webservice::METHOD_GET_SAMPLES, $args);

        self::assertArrayHasKey('request', $response);
        $request = $response['request'];

        self::assertArrayHasKey('moduleid', $request);
        self::assertSame($args['moduleid'], $request['moduleid']);

        self::assertArrayHasKey('regex', $request);
        self::assertSame($args['regex'], $request['regex']);
    }

    public function test_it_returns_a_summary() {
        $this->generator()->create_activity('Some Course', 'assignment', 'Some Assignment');
        $this->generator()->create_activity('Some Course', 'book', 'Some Book');

        $response = $this->call_webservice_successfully(
            activity_rule_webservice::METHOD_GET_SAMPLES,
            [
                'moduleid' => 0,
                'regex'    => '/^Some .*$/',
            ]
        );

        self::assertArrayHasKey('summary', $response);
        self::assertSame('2 activities found', $response['summary']);
    }

    public function test_summaries() {
        $actual = activity_rule_webservice::get_summary(0);
        self::assertContains('no ', $actual);

        $actual = activity_rule_webservice::get_summary(1);
        self::assertContains('only ', $actual);

        $actual = activity_rule_webservice::get_summary(10);
        self::assertContains('10', $actual);

        $actual = activity_rule_webservice::get_summary(100);
        self::assertContains('or more', $actual);
    }

    public function test_it_finds_all_activities() {
        $assignment = $this->generator()->create_activity('Some Course', 'assignment', 'Assignment 1');
        $book = $this->generator()->create_activity('Some Course', 'book', 'Important Book');

        $response = $this->call_webservice_successfully(
            activity_rule_webservice::METHOD_GET_SAMPLES,
            [
                'moduleid' => 0,
                'regex'    => '',
            ]
        );
        $matches = $response['matches'];

        $actual = [];
        foreach ($matches as $match) {
            $actual[$match['cmid']] = $match['name'];
        }

        self::assertSame($assignment->name, $actual[$assignment->cmid]);
        self::assertSame($book->name, $actual[$book->cmid]);
    }
    public function test_it_finds_only_assignment_activities() {
        global $DB;

        $assignment = $this->generator()->create_activity('Some Course', 'assignment', 'Assignment 1');
        $book = $this->generator()->create_activity('Some Course', 'book', 'Important Book');
        $assignid = $DB->get_field('modules', 'id', ['name' => 'assign'], MUST_EXIST);

        $response = $this->call_webservice_successfully(
            activity_rule_webservice::METHOD_GET_SAMPLES,
            [
                'moduleid' => $assignid,
                'regex'    => '',
            ]
        );
        $matches = $response['matches'];

        $actual = [];
        foreach ($matches as $match) {
            $actual[$match['cmid']] = $match['name'];
        }

        self::assertSame($assignment->name, $actual[$assignment->cmid]);
        self::assertArrayNotHasKey($book->cmid, $actual);
    }

    public function test_it_finds_only_activities_by_regex() {
        global $DB;

        $assignment1 = $this->generator()->create_activity('Some Course', 'assignment', 'Test Assign');
        $assignment2 = $this->generator()->create_activity('Some Course', 'assignment', 'My Assign');
        $book = $this->generator()->create_activity('Some Course', 'book', 'Test Book');

        $assignid = $DB->get_field('modules', 'id', ['name' => 'assign'], MUST_EXIST);
        $response = $this->call_webservice_successfully(
            activity_rule_webservice::METHOD_GET_SAMPLES,
            [
                'moduleid' => $assignid,
                'regex'    => '/^Test .*$/',
            ]
        );
        $matches = $response['matches'];

        $actual = [];
        foreach ($matches as $match) {
            $actual[$match['cmid']] = $match['name'];
        }

        self::assertSame($assignment1->name, $actual[$assignment1->cmid]);
        self::assertArrayNotHasKey($assignment2->cmid, $actual);
        self::assertArrayNotHasKey($book->cmid, $actual);
    }

    public function test_it_finds_only_assignment_by_regex() {
        $assignment = $this->generator()->create_activity('Some Course', 'assignment', 'Assignment 1');
        $importantbook = $this->generator()->create_activity('Some Course', 'book', 'Important Book');
        $assignmentbook = $this->generator()->create_activity('Some Course', 'book', 'Assignment Book');

        $response = $this->call_webservice_successfully(
            activity_rule_webservice::METHOD_GET_SAMPLES,
            [
                'moduleid' => 0,
                'regex'    => '/^Assignment .*$/',
            ]
        );
        $matches = $response['matches'];

        $actual = [];
        foreach ($matches as $match) {
            $actual[$match['cmid']] = $match['name'];
        }

        self::assertSame($assignment->name, $actual[$assignment->cmid]);
        self::assertSame($assignmentbook->name, $actual[$assignmentbook->cmid]);
        self::assertArrayNotHasKey($importantbook->cmid, $actual);
    }

    public function test_it_get_samples_only_of_visible_courses() {
        $this->generator()->create_course_by_shortname('invisible', ['visible' => 0]);
        $assignment = $this->generator()->create_activity('invisible', 'assignment', 'Assignment 1');

        $response = $this->call_webservice_successfully(
            activity_rule_webservice::METHOD_GET_SAMPLES,
            [
                'moduleid' => 0,
                'regex'    => '',
            ]
        );
        $matches = $response['matches'];
        $actual = [];
        foreach ($matches as $match) {
            $actual[$match['cmid']] = $match['name'];
        }

        self::assertArrayNotHasKey($assignment->cmid, $actual);
    }
}
