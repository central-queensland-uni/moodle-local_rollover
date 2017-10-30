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

use local_rollover\local\rollover\rollover_parameters;
use local_rollover\navigation;
use local_rollover\test\mock_navigation;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_navigation_test extends rollover_testcase {
    public function test_course_rollover_option() {
        $navigation = new mock_navigation();

        (new navigation())->add_course_administration($navigation, 1);

        /** @var moodle_url $url */
        list($name, $url) = $navigation->data[0];
        self::assertSame($name, 'Rollover');
        self::assertEquals(1, $url->param(rollover_parameters::PARAM_DESTINATION_COURSE_ID));
    }

    public function test_it_only_shows_options_user_has_capability() {
        $this->markTestSkipped('Test/Feature not yet implemented.');
    }
}
