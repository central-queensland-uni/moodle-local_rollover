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
use local_rollover\event\rollover_completed;
use local_rollover\event\rollover_started;
use local_rollover\form\steps\form_confirmation;
use local_rollover\local\rollover\rollover_controller;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_steps_rollover_complete_test extends rollover_testcase {
    public function test_it_is_used_after_last_step() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $destinationcourse = $this->generator()->create_course_by_shortname('destination');
        $sourcecourse = $this->generator()->create_course_by_shortname('from');
        $step = rollover_controller::get_step_index(rollover_controller::STEP_COMPLETE) - 1;

        $worker = backup_worker::create($sourcecourse->id);
        $worker->save();
        form_confirmation::mock_submit([
                                                rollover_parameters::PARAM_CURRENT_STEP          => $step,
                                                rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destinationcourse->id,
                                                rollover_parameters::PARAM_BACKUP_ID             => $worker->get_backup_id(),
                                            ]);
        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        self::assertContains('Rollover successful', $html);
    }

    public function test_it_generates_the_log_events() {
        $this->resetAfterTest(true);
        self::setAdminUser();
        $destination = $this->generator()->create_course_by_shortname('destination');
        $source = $this->generator()->create_course_by_shortname('from');
        $step = rollover_controller::get_step_index(rollover_controller::STEP_COMPLETE) - 1;

        $worker = backup_worker::create($source->id);
        $worker->save();
        form_confirmation::mock_submit([
                                           rollover_parameters::PARAM_CURRENT_STEP          => $step,
                                           rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destination->id,
                                           rollover_parameters::PARAM_BACKUP_ID             => $worker->get_backup_id(),
                                       ]);
        $controller = new rollover_controller();

        $sink = $this->redirectEvents();
        ob_start();
        $controller->index();
        ob_end_clean();

        $events = self::filter_rollover_events($sink->get_events());
        self::assertCount(2, $events);

        /** @var rollover_started $event */
        $event = $events[0];
        self::assertInstanceOf(rollover_started::class, $event);
        $coursecontext = context_course::instance($destination->id)->id;
        self::assertSame($destination->id, $event->get_destination_course_id(), 'rollover_started: Invalid destination.');
        self::assertSame($source->id, $event->get_source_course_id(), 'rollover_started: Invalid source.');
        self::assertSame($worker->get_backup_id(), $event->get_backup_id(), 'rollover_started: Invalid backup id.');
        self::assertSame($coursecontext, $event->get_context()->id, 'rollover_started: Invalid context.');

        /** @var rollover_completed $event */
        $event = $events[1];
        self::assertInstanceOf(rollover_completed::class, $event);
        $coursecontext = context_course::instance($destination->id)->id;
        self::assertSame($destination->id, $event->get_destination_course_id(), 'rollover_completed: Invalid destination.');
        self::assertSame($source->id, $event->get_source_course_id(), 'rollover_completed: Invalid source.');
        self::assertSame($worker->get_backup_id(), $event->get_backup_id(), 'rollover_completed: Invalid backup id.');
        self::assertSame($coursecontext, $event->get_context()->id, 'rollover_completed: Invalid context.');
        self::assertSame($worker->get_history_filename(), $event->get_filename(), 'rollover_completed: Invalid file.');
    }
}
