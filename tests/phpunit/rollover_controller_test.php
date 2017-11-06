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

use local_rollover\admin\settings_controller;
use local_rollover\form\form_source_course_selection;
use local_rollover\local\rollover\rollover_controller;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_rollover_controller_test extends rollover_testcase {
    public function test_it_shows_source_selection() {
        global $COURSE;

        $this->resetAfterTest(true);
        self::setAdminUser();
        $COURSE = $this->generator()->create_course();
        $_GET = [rollover_parameters::PARAM_DESTINATION_COURSE_ID => 1];

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        self::assertContains('Rollover: Select source course', $html);
    }

    public function test_it_shows_options_selection() {
        $this->resetAfterTest(true);
        self::setAdminUser();
        $source = $this->generator()->create_course_by_shortname('source');
        $destination = $this->generator()->create_course_by_shortname('destination');

        form_source_course_selection::mock_submit([
                                                      rollover_parameters::PARAM_SOURCE_COURSE_ID      => $source->id,
                                                      rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destination->id,
                                                  ]);

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        self::assertContains('Rollover: Select content options', $html);
    }

    public function test_it_creates_a_form_with_the_user_courses() {
        $this->resetAfterTest();

        $user = $this->generator()->create_user_by_username('someone');

        $destination = $this->generator()->create_course_by_shortname('destination');
        $modifiable = $this->generator()->create_course_by_shortname('can-modify');
        $this->generator()->create_course_by_shortname('cannot-modify');

        $this->generator()->enrol_editing_teacher('someone', 'destination');
        $this->generator()->enrol_editing_teacher('someone', 'can-modify');
        $this->generator()->enrol_nonediting_teacher('someone', 'cannot-modify');

        self::setUser($user);

        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destination->id;
        $controller = new rollover_controller();
        $form = $controller->get_step()->create_form();

        $courses = $form->get_my_courses();
        foreach ($courses as &$course) {
            $course = (array)$course;
        }

        $expected = [
            $modifiable->id => [
                'id'        => $modifiable->id,
                'shortname' => 'can-modify',
                'fullname'  => $modifiable->fullname,
            ],
        ];
        self::assertSame($expected, $courses);
    }

    public function test_it_creates_a_form_with_past_instances() {
        $this->resetAfterTest();

        $user = $this->generator()->create_user_by_username('someone');

        $destination = $this->generator()->create_course_by_shortname('test_destination');
        $previous = $this->generator()->create_course_by_shortname('test_a');
        $this->generator()->create_course_by_shortname('course_b');

        $this->generator()->enrol_editing_teacher('someone', 'test_destination');

        self::setUser($user);
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX,
                   '/^([^_]+)_.*$/',
                   'local_rollover');

        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destination->id;
        $controller = new rollover_controller();
        $form = $controller->get_step()->create_form();

        $courses = $form->get_past_instances();
        foreach ($courses as &$course) {
            $course = (array)$course;
        }

        $expected = [
            $previous->id => [
                'id'        => $previous->id,
                'shortname' => $previous->shortname,
                'fullname'  => $previous->fullname,
            ],
        ];
        self::assertSame($expected, $courses);
    }

    public function test_it_does_not_get_past_instances_not_active() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }

    public function test_it_ignores_or_fails_gracefully_if_regex_is_invalid_or_contains_no_group() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }
}
