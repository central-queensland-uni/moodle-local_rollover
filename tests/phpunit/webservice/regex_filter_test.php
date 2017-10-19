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
        $methodname = 'local_rollover_regex_filter_get_sample_matches_by_regex';
        $response = $this->call_webservice_successfully($methodname, $args);

        self::assertArrayHasKey('regex', $response);
        self::assertSame($args['regex'], $response['regex']);
    }
}
