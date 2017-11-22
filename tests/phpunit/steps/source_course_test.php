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
use local_rollover\event\rollover_requested;
use local_rollover\form\steps\form_source_course_selection;
use local_rollover\local\rollover\rollover_controller;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\local\rollover\step_source_course;
use local_rollover\test\rollover_testcase;
use Symfony\Component\DomCrawler\Crawler;

defined('MOODLE_INTERNAL') || die();

class local_rollover_steps_source_course_test extends rollover_testcase {
    public function test_it_shows_source_selection() {
        global $COURSE;

        $this->resetAfterTest(true);
        self::setAdminUser();
        $COURSE = $this->generator()->create_course();

        $step = rollover_controller::get_step_index(rollover_controller::STEP_SELECT_SOURCE_COURSE);
        $_GET = [
            rollover_parameters::PARAM_DESTINATION_COURSE_ID => 1,
            rollover_parameters::PARAM_CURRENT_STEP          => $step,
        ];

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        self::assertContains('Rollover: Select source course', $html);
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

        $step = rollover_controller::get_step_index(rollover_controller::STEP_SELECT_SOURCE_COURSE);
        $_GET = [
            rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destination->id,
            rollover_parameters::PARAM_CURRENT_STEP          => $step,
        ];

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

        $step = rollover_controller::get_step_index(rollover_controller::STEP_SELECT_SOURCE_COURSE);
        $_GET = [
            rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destination->id,
            rollover_parameters::PARAM_CURRENT_STEP          => $step,
        ];
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

    public function test_it_is_used_when_not_submitted() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $destinationcourse = $this->generator()->create_course_by_shortname('into');
        $option1 = $this->generator()->create_course(['shortname' => 'short-a', 'fullname' => 'Course A'])->id;
        $option2 = $this->generator()->create_course(['shortname' => 'short-b', 'fullname' => 'Course B'])->id;

        $step = rollover_controller::get_step_index(rollover_controller::STEP_SELECT_SOURCE_COURSE);
        $_GET = [
            rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destinationcourse->id,
            rollover_parameters::PARAM_CURRENT_STEP          => $step,
        ];

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        $crawler = new Crawler($html);

        $formname = str_replace('\\', '_', form_source_course_selection::class);
        $actual = $crawler->filter("input[name='_qf__{$formname}']")->count();
        self::assertSame(1, $actual, 'Wrong form used.');

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

    public function test_it_is_used_directly_if_no_warnings() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $this->generator()->disable_protection();

        $destinationcourse = $this->generator()->create_course_by_shortname('into');
        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destinationcourse->id;

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        $crawler = new Crawler($html);

        $formname = str_replace('\\', '_', form_source_course_selection::class);
        $actual = $crawler->filter("input[name='_qf__{$formname}']")->count();
        self::assertSame(1, $actual, 'Wrong form used.');
    }

    public function test_it_gets_past_instances() {
        $this->resetAfterTest();

        $destination = $this->generator()->create_course(['shortname' => 'ABC123']);
        $source = $this->generator()->create_course(['shortname' => 'ABC456']);
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, '/^(ABC).*$/', 'local_rollover');

        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destination->id;
        $controller = new rollover_controller();
        $step = new step_source_course($controller);
        $actual = $step->get_past_instances();
        self::assertCount(1, $actual);

        $actual = reset($actual);
        self::assertSame($source->id, $actual->id);
    }

    public function test_it_does_not_get_past_instances_not_active() {
        $this->resetAfterTest();

        $destination = $this->generator()->create_course(['shortname' => 'ABC123']);
        $this->generator()->create_course(['shortname' => 'ABC456', 'visible' => 0]);
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, '/^(ABC).*$/', 'local_rollover');

        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destination->id;
        $controller = new rollover_controller();
        $step = new step_source_course($controller);
        $actual = $step->get_past_instances();
        self::assertSame([], $actual);
    }

    public function test_it_ignores_or_fails_gracefully_if_regex_is_invalid() {
        $this->resetAfterTest();

        $destination = $this->generator()->create_course(['shortname' => 'ABC123']);
        $this->generator()->create_course(['shortname' => 'ABC456']);
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, '/^abc', 'local_rollover');

        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destination->id;
        $controller = new rollover_controller();
        $step = new step_source_course($controller);
        $actual = $step->get_past_instances();
        $this->assertDebuggingCalled();
        self::assertSame([], $actual);
    }

    public function test_it_ignores_or_fails_gracefully_if_regex_contains_no_group() {
        $this->resetAfterTest();

        $destination = $this->generator()->create_course(['shortname' => 'ABC123']);
        $this->generator()->create_course(['shortname' => 'ABC456']);
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, '/^\(ABC\).*$/', 'local_rollover');

        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destination->id;
        $controller = new rollover_controller();
        $step = new step_source_course($controller);
        $actual = $step->get_past_instances();
        self::assertSame([], $actual);
    }

    public function test_it_generates_a_log_event() {
        $this->resetAfterTest(true);
        self::setAdminUser();
        $source = $this->generator()->create_course_by_shortname('source');
        $destination = $this->generator()->create_course_by_shortname('destination');

        $step = rollover_controller::get_step_index(rollover_controller::STEP_SELECT_SOURCE_COURSE);
        form_source_course_selection::mock_submit([
                                                      rollover_parameters::PARAM_SOURCE_COURSE_ID      => $source->id,
                                                      rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destination->id,
                                                      rollover_parameters::PARAM_CURRENT_STEP          => $step,
                                                  ]);

        $controller = new rollover_controller();

        $sink = $this->redirectEvents();
        ob_start();
        $controller->index();
        ob_end_clean();

        $events = $sink->get_events();
        self::assertCount(1, $events);

        /** @var rollover_requested $event */
        $event = $events[0];
        self::assertInstanceOf(rollover_requested::class, $event);

        $coursecontext = context_course::instance($destination->id)->id;
        self::assertSame($destination->id, $event->get_destination_course_id(), 'Invalid destination course id.');
        self::assertSame($source->id, $event->get_source_course_id(), 'Invalid source course id.');
        self::assertSame($controller->get_backup_worker()->get_backup_id(), $event->get_backup_id(), 'Invalid backup id.');
        self::assertSame($coursecontext, $event->get_context()->id, 'Invalid cpmtext id.');
    }
}
