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

namespace local_rollover\backup;

use backup_activity_generic_setting;
use backup_activity_task;
use backup_task;
use local_rollover\dml\activity_rule_db;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activities_and_resources_rules_applier {
    /** @var array */
    private $rules;

    public function __construct() {
        $this->rules = (new activity_rule_db())->all();
    }

    /**
     * @param backup_task[] $tasks
     */
    public function apply($tasks) {
        foreach ($tasks as $task) {
            foreach ($task->get_settings() as $setting) {
                if (!($task instanceof backup_activity_task)) {
                    continue;
                }
                if (!($setting instanceof backup_activity_generic_setting)) {
                    continue;
                }
                $this->apply_activity_rules($task, $setting);
            }
        }
    }

    public function apply_activity_rules(backup_activity_task $task, backup_activity_generic_setting $setting) {
        global $DB;

        $activity = $DB->get_record('course_modules',
                                    ['id' => $task->get_moduleid()],
                                    'id, module',
                                    MUST_EXIST);
        $activity->name = $task->get_name();

        foreach ($this->rules as $rule) {
            if ($this->rule_matches_activity($rule, $activity)) {
                $this->apply_activity_rule($rule, $setting);
            }
        }
    }

    private function rule_matches_activity($rule, $activity) {
        if (!empty($rule->moduleid) && ($rule->moduleid != $activity->module)) {
            return false;
        }

        if (!empty($rule->regex) && !preg_match($rule->regex, $activity->name)) {
            return false;
        }

        return true;
    }

    private function apply_activity_rule($rule, backup_activity_generic_setting $setting) {
        if (($rule->rule == activity_rule_db::RULE_FORBID) || ($rule->rule == activity_rule_db::RULE_NOT_DEFAULT)) {
            $setting->set_value(0);
        }
        if (($rule->rule == activity_rule_db::RULE_FORBID) || ($rule->rule == activity_rule_db::RULE_ENFORCE)) {
            $setting->get_ui()->set_changeable(false);
        }
    }
}
