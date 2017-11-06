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
use local_rollover\regex_validator;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../lib/externallib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_rule_webservice extends external_api {
    const MAX_SAMPLES = 50;

    const METHOD_GET_SAMPLES = 'local_rollover_activity_rule_samples';

    public static function get_samples($moduleid, $regex) {
        self::validate_parameters(self::get_samples_parameters(), compact('moduleid', 'regex'));

        $validator = new regex_validator($regex);

        if ($validator->is_valid()) {
            $regexerror = '';
            $modules = self::find_all_modules_names($moduleid, $regex);
            $matches = [];
            foreach ($modules as $cmid => $name) {
                $matches[] = compact('cmid', 'name');
            }
        } else {
            $regexerror = $validator->get_error();
            $matches = [];
        }

        return [
            'request'    => [
                'moduleid' => $moduleid,
                'regex'    => $regex,
            ],
            'regexerror' => $regexerror,
            'matches'    => $matches,
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
        $regexerror = new external_value(PARAM_TEXT, 'RegEx error message or empty for no error.');

        $moduleid = new external_value(PARAM_INT, 'Requested module id.');
        $regex = new external_value(PARAM_TEXT, 'Requested regex.');
        $request = new external_single_structure(compact('moduleid', 'regex'), 'Request parameters.');

        $cmid = new external_value(PARAM_TEXT, 'Course module id.');
        $name = new external_value(PARAM_TEXT, 'Course module name.');
        $match = new external_single_structure(compact('cmid', 'name'), 'Course module (activity) match.');
        $matches = new external_multiple_structure($match, 'Course modules (activities) matched.');

        return new external_single_structure(compact('regexerror', 'request', 'matches'),
                                             'Result for the request.');
    }

    private static function find_all_modules_names($moduleid, $regex) {
        global $DB;

        $conditions = ['visible' => 1];
        if (!empty($moduleid)) {
            $conditions['id'] = $moduleid;
        }
        $modules = $DB->get_records('modules', $conditions, 'name ASC', 'id, name');

        $names = [];
        foreach ($modules as $module) {
            self::add_all_names_for_a_module($names, $module, $regex);
            if (count($names) >= self::MAX_SAMPLES) {
                break;
            }
        }

        return array_flip($names);
    }

    private static function add_all_names_for_a_module(&$names, $module, $regex) {
        global $DB;

        $sql = "
            SELECT cm.id AS cmid, m.name
            FROM {course_modules} AS cm
            INNER JOIN {{$module->name}} AS m ON cm.instance=m.id
            INNER JOIN {course} AS course ON course.id=cm.course
            WHERE course.visible=1 AND cm.module=?
            ORDER BY m.name ASC
        ";
        $instances = $DB->get_records_sql($sql, [$module->id]);
        foreach ($instances as $instance) {
            $name = $instance->name;
            if (array_key_exists($name, $names)) {
                continue;
            }
            if (empty($regex) || preg_match($regex, $name)) {
                $names[$name] = $instance->cmid;
            }
            if (count($names) >= self::MAX_SAMPLES) {
                break;
            }
        }

        return $names;
    }
}
