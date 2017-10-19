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

namespace local_rollover\webservice;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../lib/externallib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_regex_filter_webservice extends external_api {
    public static function get_sample_matches_by_regex($regex) {
        self::validate_parameters(self::get_sample_matches_by_regex_parameters(), compact('regex'));

        return ['regex' => $regex];
    }

    public static function get_sample_matches_by_regex_parameters() {
        return new external_function_parameters(
            ['regex' => new external_value(PARAM_TEXT, 'Regular Expression for matching.')]
        );
    }

    public static function get_sample_matches_by_regex_returns() {
        return new external_single_structure(
            [
                'regex' => new external_value(PARAM_TEXT, 'Provided Regular Expression.'),
            ],
            'Result for the request.'
        );
    }
}
