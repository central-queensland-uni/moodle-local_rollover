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

use local_rollover\admin\settings_controller;
use local_rollover\dml\activity_rule_db;
use local_rollover\test\mock_admintree;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_admin_settings_test extends rollover_testcase {
    public function test_it_has_all_settings() {
        $ADMIN = new mock_admintree();
        require(__DIR__ . '/../../../settings.php');

        $expected = [
            'local_rollover'                => 'courses',
            'local_rollover_options'        => 'local_rollover',
            'local_rollover_filter'         => 'local_rollover',
            'local_rollover_activities'     => 'local_rollover',
            'local_rollover_protection'     => 'local_rollover',
            'local_rollover_backup_history' => 'local_rollover',
        ];
        self::assertSame($expected, $ADMIN->tree);
    }

    public function provider_for_test_it_rule_sentences() {
        $rf = activity_rule_db::RULE_FORBID;
        $re = activity_rule_db::RULE_ENFORCE;
        $rn = activity_rule_db::RULE_NOT_DEFAULT;

        $a0 = null;
        $a1 = 1;

        $r0 = '';
        $r1 = '/^regex$/';

        return [
            [$rf, $a0, $r0, "Forbid rolling over all activities."],
            [$rf, $a0, $r1, "Forbid rolling over any activity matching:"],
            [$rf, $a1, $r0, "Forbid rolling over all 'Assignment' activities."],
            [$rf, $a1, $r1, "Forbid rolling over any 'Assignment' matching:"],
            [$re, $a0, $r0, "Enforce rolling over all activities."],
            [$re, $a0, $r1, "Enforce rolling over any activity matching:"],
            [$re, $a1, $r0, "Enforce rolling over all 'Assignment' activities."],
            [$re, $a1, $r1, "Enforce rolling over any 'Assignment' matching:"],
            [$rn, $a0, $r0, "Do not rollover by default any activity."],
            [$rn, $a0, $r1, "Do not rollover by default any activity matching:"],
            [$rn, $a1, $r0, "Do not rollover by default any 'Assignment' activity."],
            [$rn, $a1, $r1, "Do not rollover by default any 'Assignment' matching:"],
        ];
    }

    /**
     * @dataProvider provider_for_test_it_rule_sentences
     */
    public function test_it_rule_sentences($rule, $moduleid, $regex, $expected) {
        $rule = (object)compact('rule', 'moduleid', 'regex');
        $actual = settings_controller::create_rule_sentence($rule);
        $actual = strip_tags($actual);
        self::assertSame($expected, $actual);
    }
}
