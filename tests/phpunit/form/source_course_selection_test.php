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

use local_rollover\form\form_source_course_selection;
use local_rollover\test\rollover_testcase;
use Symfony\Component\DomCrawler\Crawler;

defined('MOODLE_INTERNAL') || die();

class local_rollover_form_source_course_selection_test extends rollover_testcase {
    public function test_it_renders() {
        $form = new form_source_course_selection();

        ob_start();
        $form->display();
        $html = ob_get_clean();

        self::assertContains('<form', $html);
    }

    public function test_it_shows_the_given_courses() {
        $courses = [
            1 => (object)[
                'id'        => '1',
                'shortname' => 'course-a',
                'fullname'  => 'Course A',
            ],
            2 => (object)[
                'id'        => '2',
                'shortname' => 'course-b',
                'fullname'  => 'Course B',
            ],
        ];

        $form = new form_source_course_selection($courses);

        ob_start();
        $form->display();
        $html = ob_get_clean();

        $crawler = new Crawler($html);
        $found = $crawler->filter('#local_rollover-your_units')->html();

        self::assertContains('course-a', $found);
        self::assertContains('course-b', $found);
    }
}
