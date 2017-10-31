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
 * @var stdClass $plugin
 */

use local_rollover\webservice\activity_rule_webservice;
use local_rollover\webservice\course_regex_filter_webservice;

defined('MOODLE_INTERNAL') || die();

$services = [
    'local_rollover_regex_filter'  => [
        'functions' => [
            'local_rollover_regex_filter_get_sample_matches_by_regex',
        ],
        'restrictedusers' => 0,
        'enabled'   => 1,
    ],
    'local_rollover_activity_rule' => [
        'functions'       => [],
        'restrictedusers' => 0,
        'enabled'         => 1,
    ],
];

$functions = [
    'local_rollover_regex_filter_get_sample_matches_by_regex' => [
        'classname'   => course_regex_filter_webservice::class,
        'methodname'  => 'get_sample_matches_by_regex',
        'classpath'   => 'local/rollover/classes/webservice/course_regex_filter_webservice.php',
        'description' => 'Fetches real examples of matches of course shortname for a given filter regex.',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'local_rollover_activity_rule_samples'                    => [
        'classname'   => activity_rule_webservice::class,
        'methodname'  => 'get_samples',
        'classpath'   => 'local/rollover/classes/webservice/activity_rule_webservice.php',
        'description' => 'Fetches real examples of activities that would match a given rule.',
        'type'        => 'read',
        'ajax'        => true,
    ],
];
