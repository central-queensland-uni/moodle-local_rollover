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

use local_rollover\local\protection;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_protection_test extends rollover_testcase {
    /** @var stdClass */
    private $course = null;

    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->course = $this->generator()->create_course_by_shortname('protection');
    }

    public function provider_for_protection_actions() {
        return [
            [protection::ACTION_IGNORE],
            [protection::ACTION_WARN],
            [protection::ACTION_STOP],
        ];
    }

    /**
     * @dataProvider provider_for_protection_actions
     */
    public function test_it_always_skips_check_if_destination_course_empty($action) {
        protection::set_config(protection::PROTECT_NOT_EMPTY, $action);

        $protector = new protection($this->course);
        $actual = $protector->check_empty();

        self::assertSame($protector::ACTION_IGNORE, $actual, "Action: {$action}");
    }

    /**
     * @dataProvider provider_for_protection_actions
     */
    public function test_it_checks_if_destination_course_empty($action) {
        $this->generator()->create_activity($this->course->shortname, 'assignment', 'An assignment');

        protection::set_config(protection::PROTECT_NOT_EMPTY, $action);

        $protector = new protection($this->course);

        $actual = $protector->check_empty();
        self::assertSame($action, $actual);
    }

    /**
     * @dataProvider provider_for_protection_actions
     */
    public function test_it_always_skips_check_if_destination_course_hidden($action) {
        protection::set_config(protection::PROTECT_NOT_HIDDEN, $action);
        $this->course->visible = '0';

        $protector = new protection($this->course);
        $actual = $protector->check_hidden();

        self::assertSame($protector::ACTION_IGNORE, $actual, "Action: {$action}");
    }

    /**
     * @dataProvider provider_for_protection_actions
     */
    public function test_it_checks_if_destination_course_hidden($action) {
        protection::set_config(protection::PROTECT_NOT_HIDDEN, $action);

        $protector = new protection($this->course);

        $actual = $protector->check_hidden();
        self::assertSame($action, $actual);
    }

    /**
     * @dataProvider provider_for_protection_actions
     */
    public function test_it_always_skips_check_if_destination_course_has_not_started($action) {
        protection::set_config(protection::PROTECT_HAS_STARTED, $action);
        $this->course->startdate = time() + DAYSECS; // Starts tomorrow.

        $protector = new protection($this->course);
        $actual = $protector->check_started();

        self::assertSame($protector::ACTION_IGNORE, $actual, "Action: {$action}");
    }

    /**
     * @dataProvider provider_for_protection_actions
     */
    public function test_it_checks_if_destination_course_already_started($action) {
        protection::set_config(protection::PROTECT_HAS_STARTED, $action);
        $this->course->startdate = time() - DAYSECS; // Started yesterday.

        $protector = new protection($this->course);

        $actual = $protector->check_started();
        self::assertSame($action, $actual);
    }
}
