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
 * @codingStandardsIgnoreFile
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Gherkin\Node\TableNode;
use local_rollover\admin\rollover_settings;
use local_rollover\admin\settings_controller;
use local_rollover\dml\activity_rule_db;
use local_rollover\local\protection;
use local_rollover\test\generator;

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait local_rollover_behat_context_definition_for_data_generation {
    /** @var generator */
    private $generator = null;

    /**
     * @return generator
     */
    public function generator() {
        if (is_null($this->generator)) {
            $this->generator = new generator();
        }
        return $this->generator;
    }

    /**
     * @Given /^there is a course with shortname "([^"]*)" +\# local_rollover$/
     */
    public function thereIsACourseWithShortname($shortname) {
        $this->generator()->create_course_by_shortname($shortname);
    }

    /**
     * @Given /^the course "([^"]*)" has an? ([a-z]+) "([^"]*)" +\# local_rollover$/
     */
    public function theCourseHasAnActivity($shortname, $activity, $name) {
        $this->generator()->create_activity($shortname, $activity, $name);
    }

    /**
     * @Given /^I (can|cannot) modify the following courses: +\# local_rollover$/
     */
    public function iCanModifyTheFollowingCourses($canornot, TableNode $courses) {
        $courses = $courses->getColumn(0);
        foreach ($courses as $course) {
            $this->iCanModifyTheTheCourse($canornot, $course);
        }
    }

    /**
     * @Given /^I (can|cannot) modify the the course "([^"]*)" +\# local_rollover$/
     */
    public function iCanModifyTheTheCourse($canornot, $course) {
        $this->generator()->create_course_by_shortname($course);
        if ($canornot == 'can') {
            $this->generator()->enrol_editing_teacher($this->myusername, $course);
        }
    }

    /**
     * @Given /^the course "([^"]*)" has a student "([^"]*)" +\# local_rollover$/
     */
    public function theCourseHasAStudent($course, $user) {
        $this->generator()->enrol_student($user, $course);
    }

    /**
     * @Given /^the default rollover settings do not include anything by default +\# local_rollover$/
     */
    public function theDefaultRolloverSettingsDoNotIncludeAnythingByDefault() {
        $options = array_keys(rollover_settings::get_rollover_options_defaults());
        foreach ($options as $option) {
            set_config("option_{$option}", 0, 'local_rollover');
        }
    }

    /**
     * @Given /^the course "([^"]*)" has an HTML block "([^"]*)" +\# local_rollover$/
     */
    public function theCourseHasAnHTMLBlock($course, $text) {
        $this->generator()->create_html_block($course, $text);
    }

    /**
     * @Given /^the past instances RegEx is set to "([^"]*)" +\# local_rollover$/
     */
    public function thePastInstancesRegExIsSetTo($regex) {
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, $regex, 'local_rollover');
    }

    /**
     * @Given /^the following activity rollover rules? exists?: +\# local_rollover$/
     */
    public function theFollowingActivityRolloverRulesExist(TableNode $rules) {
        global $DB;
        $dml = new activity_rule_db();

        foreach ($rules->getColumnsHash() as $rule) {
            $rule = (object)$rule;

            switch ($rule->rule) {
                case 'forbid':
                    $rule->rule = activity_rule_db::RULE_FORBID;
                    break;
                case 'enforce':
                    $rule->rule = activity_rule_db::RULE_ENFORCE;
                    break;
                case 'not default':
                    $rule->rule = activity_rule_db::RULE_NOT_DEFAULT;
                    break;
                default:
                    throw new moodle_exception("Invalid rule: {$rule->rule}");
            }

            $rule->activity = strtolower($rule->activity);
            if (empty($rule->activity)) {
                $rule->moduleid = null;
            } else {
                if ($rule->activity == 'assignment') {
                    $rule->activity = 'assign';
                }
                $rule->moduleid = $DB->get_field('modules', 'id', ['name' => $rule->activity], MUST_EXIST);
            }
            unset($rule->activity);

            $dml->save($rule);
        }
    }

    /**
     * @Given /^the rollover protection is configured as follows: +\# local_rollover$/
     */
    public function theRolloverProtectionIsConfiguredAsFollows(TableNode $table) {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            $option = $this->get_option_for_text($row['Protection']);
            protection::set_config($option, $row['Action']);
        }
    }

    private function get_option_for_text($text) {
        switch ($text) {
            case 'If rollover destination is not empty':
                return protection::PROTECT_NOT_EMPTY;
            case 'If rollover destination is not hidden':
                return protection::PROTECT_NOT_HIDDEN;
            case 'If rollover destination contains user data':
                return protection::PROTECT_HAS_USER_DATA;
            case 'If rollover destination has already started':
                return protection::PROTECT_HAS_STARTED;
            default:
                throw new moodle_exception("Invalid text: {$text}");
        }
    }

    /**
     * @Given /^the "([^"]*)" course is not empty, is visible, has user data and has already started +\# local_rollover$/
     */
    public function theCourseIsNotEmptyIsVisibleHasUserDataAndHasAlreadyStarted($course) {
        $this->generator()->create_activity($course, 'assignment', 'An activity');
    }

    /**
     * @Given /^all rollover protections are disabled +\# local_rollover$/
     */
    public function allRolloverProtectionsAreDisabled() {
        $this->generator()->disable_protection();
    }
}
