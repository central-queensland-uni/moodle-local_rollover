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
use local_rollover\local\rollover\rollover_controller;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\test\rollover_testcase;
use Symfony\Component\DomCrawler\Crawler;

defined('MOODLE_INTERNAL') || die();

class local_rollover_steps_source_course_test extends rollover_testcase {
    public function test_it_is_used_when_not_submitted() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $destinationcourse = $this->generator()->create_course_by_shortname('into');
        $option1 = $this->generator()->create_course(['shortname' => 'short-a', 'fullname' => 'Course A'])->id;
        $option2 = $this->generator()->create_course(['shortname' => 'short-b', 'fullname' => 'Course B'])->id;
        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destinationcourse->id;

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        $crawler = new Crawler($html);

        $formname = str_replace('\\', '_', form_source_course_selection::class);
        $actual = $crawler->filter("input[name='_qf__{$formname}']")->count();
        self::assertSame(1, $actual, 'Wrong form used.');

        $selector1 = 'input[name="' . rollover_parameters::PARAM_DESTINATION_COURSE_ID . '"]';
        $actual = $crawler->filter($selector1)->getNode(0)->getAttribute('value');
        self::assertSame((string)$destinationcourse->id, $actual, 'Must provide destination course id.');

        $selector = 'input[name="' . rollover_parameters::PARAM_CURRENT_STEP . '"]';
        $actual = $crawler->filter($selector)->getNode(0)->getAttribute('value');
        self::assertSame('0', $actual, 'It is the first step.');

        $actual = $crawler->filter('select[name="' . rollover_parameters::PARAM_SOURCE_COURSE_ID . '"]')->count();
        self::assertSame(1, $actual, 'Missing source course field.');

        $actual = $crawler->filter('select[name="' . rollover_parameters::PARAM_SOURCE_COURSE_ID . '"] option')->count();
        self::assertSame(2, $actual, 'Source course options count is not invalid.');

        $selector = 'select[name="' . rollover_parameters::PARAM_SOURCE_COURSE_ID . '"] option[value="' . $option1 . '"]';
        $actual = $crawler->filter($selector)->text();
        self::assertContains('short-a', $actual, 'Shortname for course 1 not found.');

        $selector = 'select[name="' . rollover_parameters::PARAM_SOURCE_COURSE_ID . '"] option[value="' . $option2 . '"]';
        $actual = $crawler->filter($selector)->text();
        self::assertContains('short-b', $actual, 'Shortname for course 2 not found.');
    }

    public function test_it_works_if_validation_fails() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }
}
