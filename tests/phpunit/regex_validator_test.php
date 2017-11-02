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

use local_rollover\regex_validator;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_regex_validator_test extends rollover_testcase {
    public function provider_for_test_it_validates_the_regex() {
        return [
            'Empty RegEx is valid.'            => ['', true],
            'Capture-all RegEx is valid.'      => ['/^(.*)$/', true],
            'Ignore case flag is valid.'       => ['/^(.*)$/i', true],
            'RegEx is too short.'              => ['.', false],
            'RegEx must match beginning.'      => ['/(.*)$/', false],
            'RegEx must match end.'            => ['/^(.*)/', false],
            'RegEx must have a capture group.' => ['/^.*$/', false],
            'Malformed RegEx.'                 => ['/^a(.*b$/', false],
        ];
    }

    /**
     * @dataProvider provider_for_test_it_validates_the_regex
     */
    public function test_it_validates_the_regex($regex, $acceptable) {
        $validator = new regex_validator($regex, [regex_validator::OPTION_REQUIRE_CAPTURE_GROUP]);

        $error = $validator->get_error();
        self::assertSame($acceptable, $validator->is_valid(), "{$regex} -> {$error}");
    }

    public function test_it_validates_without_capture_group() {
        $validator = new regex_validator('/^abc$/');
        self::assertSame(true, $validator->is_valid());
        self::assertNull($validator->get_error());
    }
}
