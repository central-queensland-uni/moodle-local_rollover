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

defined('MOODLE_INTERNAL') || die();

class local_rollover_generator_test extends rollover_testcase {
    public function test_it_generates_html_blocks_for_courses() {
        self::resetAfterTest(true);

        $course = $this->generator()->create_course_by_shortname('coursename');
        $actual = $this->generator()->create_html_block('coursename', 'My HTML block!');
        $actual->configdata = (array)unserialize(base64_decode($actual->configdata));

        $configdata = [
            'text'   => '<p>My HTML block!</p>',
            'title'  => 'My HTML block!',
            'format' => '1',
        ];

        $expected = [
            'id'                => $actual->id,
            'blockname'         => 'html',
            'parentcontextid'   => (string)context_course::instance($course->id)->id,
            'showinsubcontexts' => '0',
            'requiredbytheme' => '0',
            'pagetypepattern'   => 'course-view-*',
            'subpagepattern'    => null,
            'defaultregion'     => 'side-pre',
            'defaultweight'     => '0',
            'configdata'        => $configdata,
            'timecreated'       => (string)time(),
            'timemodified'      => (string)time(),
        ];
        self::assertSame($expected, (array)$actual);
    }
}
