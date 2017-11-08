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
use local_rollover\local\rollover\rollover_controller;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\local\rollover\step_precheck;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_steps_precheck_test extends rollover_testcase {
    public function test_it_is_used_at_first() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        rollover_settings::set_protection_config(rollover_settings::PROTECTION_NOT_EMPTY,
                                                 rollover_settings::LEVEL_WARN);

        $destinationcourse = $this->generator()->create_course_by_shortname('precheck_test');
        $this->generator()->create_activity('precheck_test', 'assignment', 'An assignment');
        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destinationcourse->id;

        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        self::assertContains('Rollover: Pre-check', $html);
    }

    public function provider_for_protection_level() {
        return [
            [rollover_settings::LEVEL_IGNORE],
            [rollover_settings::LEVEL_WARN],
        ];
    }

    /**
     * @dataProvider provider_for_protection_level
     */
    public function test_it_always_skips_check_if_destination_course_empty($level) {
        $this->resetAfterTest(true);
        self::setAdminUser();
        rollover_settings::set_protection_config(rollover_settings::PROTECTION_NOT_EMPTY, $level);

        $destinationcourse = $this->generator()->create_course_by_shortname('precheck_test');
        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destinationcourse->id;

        $controller = new rollover_controller();
        $precheck = new step_precheck($controller);

        $actual = $precheck->check_skipped_empty();
        self::assertTrue($actual, "Level: {$level}");
    }

    /**
     * @dataProvider provider_for_protection_level
     */
    public function test_it_checks_if_destination_course_empty($level) {
        $this->resetAfterTest(true);
        self::setAdminUser();
        rollover_settings::set_protection_config(rollover_settings::PROTECTION_NOT_EMPTY, $level);

        $destinationcourse = $this->generator()->create_course_by_shortname('precheck_test');
        $this->generator()->create_activity('precheck_test', 'assignment', 'An assignment');
        $_GET[rollover_parameters::PARAM_DESTINATION_COURSE_ID] = $destinationcourse->id;

        $controller = new rollover_controller();
        $precheck = new step_precheck($controller);

        $expected = ($level == rollover_settings::LEVEL_IGNORE) ? true : false;
        $actual = $precheck->check_skipped_empty();
        self::assertSame($expected, $actual, "Level: {$level}");
    }
}
