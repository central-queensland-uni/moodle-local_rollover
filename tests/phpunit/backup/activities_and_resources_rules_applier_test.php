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

use local_rollover\backup\activities_and_resources_rules_applier;
use local_rollover\dml\activity_rule_db;
use local_rollover\test\mock_backup_activity_task;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

class local_rollover_backup_activities_and_resources_rules_applier_test extends rollover_testcase {
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
    }

    private function apply_activity_rule($activity) {
        $task = new mock_backup_activity_task($activity->name, $activity->cmid);
        $setting = new backup_activity_generic_setting('activity', backup_activity_generic_setting::IS_BOOLEAN);
        $applier = new activities_and_resources_rules_applier();

        $setting->set_value(1);
        $applier->apply_activity_rules($task, $setting);

        return ['value' => $setting->get_value(), 'changeable' => $setting->get_ui()->is_changeable()];
    }

    private function assert_applied_rule($activitytype, $name, $appliedrule) {
        $activity = $this->generator()->create_activity('course', $activitytype, $name);
        $actual = $this->apply_activity_rule($activity);

        $expectselected = (is_null($appliedrule) || ($appliedrule == activity_rule_db::RULE_ENFORCE));
        $expectchangeable = (is_null($appliedrule) || ($appliedrule == activity_rule_db::RULE_NOT_DEFAULT));

        self::assertSame($expectselected, (bool)$actual['value'], "Activity '{$activitytype}' named '{$name}' has invalid value.");
        self::assertSame($expectchangeable, $actual['changeable'], "Activity '{$activitytype}' named '{$name}' has invalid value.");
    }

    public function provider_for_tests_with_rules() {
        return [
            [activity_rule_db::RULE_FORBID],
            [activity_rule_db::RULE_ENFORCE],
            [activity_rule_db::RULE_NOT_DEFAULT],
        ];
    }

    public function test_it_does_not_default_or_lock_anything() {
        $this->assert_applied_rule('assignment', 'Assignment', null);
    }

    /**
     * @dataProvider provider_for_tests_with_rules
     */
    public function test_it_sets_all_not_default($rule) {
        $this->generator()->create_activity_rule($rule, null, '');

        $this->assert_applied_rule('assignment', 'Research', $rule);
        $this->assert_applied_rule('forum', 'Discussion', $rule);
    }

    /**
     * @dataProvider provider_for_tests_with_rules
     */
    public function test_it_sets_all_assignments($rule) {
        $this->generator()->create_activity_rule($rule, 'assignment', '');

        $this->assert_applied_rule('assignment', 'Research', $rule);
        $this->assert_applied_rule('forum', 'Discussion', null);
    }

    /**
     * @dataProvider provider_for_tests_with_rules
     */
    public function test_it_sets_all_activities_based_on_regex($rule) {
        $this->generator()->create_activity_rule($rule,
                                                 null,
                                                 '/^Test .*$/');

        $this->assert_applied_rule('assignment', 'Not Test Assignment', null);
        $this->assert_applied_rule('assignment', 'Test Assignment', $rule);
        $this->assert_applied_rule('forum', 'Test Forum', $rule);
    }

    /**
     * @dataProvider provider_for_tests_with_rules
     */
    public function test_it_sets_all_assignments_based_on_regex($rule) {
        $this->generator()->create_activity_rule($rule,
                                                 'assignment',
                                                 '/^Test .*$/');

        $this->assert_applied_rule('assignment', 'Not Test Assignment', null);
        $this->assert_applied_rule('assignment', 'Test Assignment', $rule);
        $this->assert_applied_rule('forum', 'Test Forum', null);
    }
}
