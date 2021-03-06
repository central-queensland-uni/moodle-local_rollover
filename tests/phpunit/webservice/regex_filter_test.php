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
use local_rollover\webservice\course_regex_filter_webservice;

defined('MOODLE_INTERNAL') || die();

class local_rollover_webservice_regex_filter_test extends rollover_testcase {
    public function test_the_webservice_exists() {
        $services = null;
        require(__DIR__ . '/../../../db/services.php');
        self::assertArrayHasKey('local_rollover_regex_filter', $services);
    }

    public function test_it_returns_the_original_regex() {
        $this->resetAfterTest();
        self::setAdminUser();

        $args = ['regex' => '/^a(bc)d$/'];
        $response = $this->call_webservice_successfully( course_regex_filter_webservice::METHOD_GET_SAMPLE_MATCHES, $args);

        self::assertArrayHasKey('regex', $response);
        self::assertSame($args['regex'], $response['regex']);
    }

    public function test_it_returns_a_summary() {
        $this->resetAfterTest();
        self::setAdminUser();

        $this->generator()->create_course_by_shortname('ABC-123');
        $this->generator()->create_course_by_shortname('DEF-987');

        $methodname = 'local_rollover_regex_filter_get_sample_matches_by_regex';
        $regex = '/^(.{3})-\d{3}$/';
        $response = $this->call_webservice_successfully($methodname, ['regex' => $regex]);

        self::assertArrayHasKey('summary', $response);
        self::assertSame('2 groups found', $response['summary']);
    }

    public function test_summaries() {
        $actual = course_regex_filter_webservice::get_summary(0);
        self::assertContains('no ', $actual);

        $actual = course_regex_filter_webservice::get_summary(1);
        self::assertContains('only ', $actual);

        $actual = course_regex_filter_webservice::get_summary(20);
        self::assertContains('20', $actual);
    }

    public function test_it_finds_the_courses() {
        $this->resetAfterTest();
        self::setAdminUser();

        $shortnames = ['ABC-123', 'ABC-987', 'ABC-hello', 'DEF-456'];
        foreach ($shortnames as $shortname) {
            $this->generator()->create_course_by_shortname($shortname);
        }

        // We are not using a provider to avoid recreating courses every test.
        $all = [];
        foreach ($shortnames as $shortname) {
            $all[$shortname] = [$shortname];
        }
        $regexes = [
            'No matches'       => ['/^()$/', []],
            'All matches'      => ['/^(.*)$/', $all],
            'Only ABC-###'     => ['/^(ABC)-\d{3}$/', ['ABC' => ['ABC-123', 'ABC-987']]],
            'By 3 first chars' => ['/^(.{3})-.*$/', ['ABC' => ['ABC-123', 'ABC-987', 'ABC-hello'], 'DEF' => ['DEF-456']]],
        ];

        $methodname = 'local_rollover_regex_filter_get_sample_matches_by_regex';

        foreach ($regexes as $description => $test) {
            list($regex, $expect) = $test;
            $response = $this->call_webservice_successfully($methodname, ['regex' => $regex]);
            $found = [];
            foreach ($response['groups'] as $group) {
                $found[$group['match']] = $group['shortnames'];
            }
            self::assertSame('', $response['regexerror'], $description);
            self::assertSame($expect, $found, $description);
        }
    }

    public function test_it_gets_an_error() {
        $this->resetAfterTest();
        self::setAdminUser();

        $methodname = 'local_rollover_regex_filter_get_sample_matches_by_regex';
        $response = $this->call_webservice_successfully($methodname, ['regex' => '/invalidregex']);
        self::assertContains('malformed', $response['regexerror']);
        self::assertSame([], $response['groups']);
    }
}
