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

namespace local_rollover;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class regex_validator {
    private function __construct() {
        // Cannot instantiate.
    }

    private static function get_regex_error($regex) {
        if ($regex == '') {
            return null;
        }
        if (strlen($regex) < 2) {
            return 'too_short';
        }
        if ($regex[1] != '^') {
            return 'invalid_start';
        }
        if (substr($regex, -2, 1) != '$') {
            return 'invalid_end';
        }
        if (strpos($regex, '(') === false) {
            return 'no_capture';
        }
        if (@preg_match($regex, null) === false) {
            return 'malformed';
        }
        return null;
    }

    public static function validation($regex) {
        $error = self::get_regex_error($regex);

        if (!is_null($error)) {
            $error = get_string("regex_error_{$error}", 'local_rollover');
        }

        return $error;
    }
}
