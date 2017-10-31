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
use external_multiple_structure;
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
class activity_rule_webservice extends external_api {
    public static function get_samples($moduleid, $regex) {
        self::validate_parameters(self::get_samples_parameters(), compact('moduleid', 'regex'));

        return [
            'request' => [
                'moduleid' => $moduleid,
                'regex'    => $regex,
            ],
            'matches' => [],
        ];
    }

    public static function get_samples_parameters() {
        return new external_function_parameters(
            [
                'moduleid' => new external_value(PARAM_INT, 'Type of module to match or 0 for all.'),
                'regex'    => new external_value(PARAM_TEXT, 'Regular Expression for matching or empty for all.'),
            ]
        );
    }

    public static function get_samples_returns() {
        $moduleid = new external_value(PARAM_INT, 'Requested module id.');
        $regex = new external_value(PARAM_TEXT, 'Requested regex.');
        $request = new external_single_structure(compact('moduleid', 'regex'), 'Request parameters.');

        $cmid = new external_value(PARAM_TEXT, 'Course module id.');
        $name = new external_value(PARAM_TEXT, 'Course module name.');
        $match = new external_single_structure(compact('cmid', 'name'), 'Course module (activity) match.');
        $matches = new external_multiple_structure($match, 'Course modules (activities) matched.');

        return new external_single_structure(compact('request', 'matches'), 'Result for the request.');
    }
}
