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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use local_rollover\admin\rollover_settings;
use local_rollover\admin\settings_controller;
use local_rollover\rollover_parameters;
use local_rollover\test\generator;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @SuppressWarnings(public) Allow as many methods as needed.
 */
class behat_local_rollover extends behat_base {
    /** @var generator */
    private $generator = null;

    /** @var string */
    protected $myusername = null;

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
    public function there_is_a_course_with_shortname($shortname) {
        $this->generator()->create_course_by_shortname($shortname);
    }

    /**
     * @Given /^I (?:am at|go to) the course "([^"]*)" page +\# local_rollover$/
     */
    public function i_the_course_page($shortname) {
        $this->visitPath('/course/view.php?name=' . $shortname);
    }

    /**
     * @Given /^I am an? (administrator|teacher) +\# local_rollover$/
     */
    public function i_am_a($user) {
        if ($user == 'administrator') {
            $user = 'admin';
        } else {
            $this->generator()->create_user_by_username($user);
        }
        $this->myusername = $user;

        $this->execute('behat_auth::i_log_in_as', [$user]);
    }

    /**
     * @When /^I press "([^"]*)" in the Course Administration block +\# local_rollover$/
     */
    public function i_press_in_the_course_administration_block($link) {
        $parentnode = array_map('trim', explode('>', $link));
        $link = array_pop($parentnode);
        if (count($parentnode) == 0) {
            $this->execute('behat_general::i_click_on_in_the', [$link, 'link', 'Administration', 'block']);
        } else {
            array_unshift($parentnode, 'Course administration');
            $parentnode = implode(' > ', $parentnode);
            $this->execute('behat_navigation::i_navigate_to_node_in', [$link, $parentnode]);
        }
    }

    /**
     * @Given /^the course "([^"]*)" has an assignment "([^"]*)" +\# local_rollover$/
     */
    public function the_course_has_an_assignment($shortname, $assignment) {
        $this->generator()->create_assignment($shortname, $assignment);
    }

    /**
     * @Given /^I (can|cannot) modify the following courses: +\# local_rollover$/
     */
    public function i_modify_the_following_courses($canornot, TableNode $courses) {
        $can = ($canornot == 'can');
        $courses = $courses->getColumn(0);

        foreach ($courses as $course) {
            $this->generator()->create_course_by_shortname($course);
            if ($can) {
                $this->generator()->enrol_editing_teacher($this->myusername, $course);
            }
        }
    }

    /**
     * @When /^I go to the rollover page for the course "([^"]*)" +\# local_rollover$/
     */
    public function i_go_to_the_rollover_page_for_the_course($shortname) {
        $param = rollover_parameters::PARAM_DESTINATION_COURSE_ID . '=' . $this->generator()->get_course_id($shortname);
        $this->visitPath("/local/rollover/index.php?{$param}");
    }

    /**
     * @Then /^I (should(?: not)?) see the following source options: +\# local_rollover$/
     */
    public function i_see_the_following_source_options($shouldornot, TableNode $courses) {
        $courses = $courses->getColumn(0);
        $shouldnot = ($shouldornot == 'should') ? '' : 'not_';
        $context = "behat_general::assert_element_{$shouldnot}contains_text";

        foreach ($courses as $course) {
            $this->execute(
                $context,
                [$course, '#local_rollover-your_units', 'css_element']
            );
        }
    }

    /**
     * @Given /^I select "([^"]*)" in "([^"]*)" +\# local_rollover$/
     */
    public function i_select_in_field($option, $field) {
        $this->execute('behat_forms::i_set_the_field_to', [$field, $option]);
    }

    /**
     * @Given /^I set the following default options: +\# local_rollover$/
     */
    public function i_set_the_following_default_options(TableNode $options) {
        foreach ($options->getHash() as $option) {
            $field = behat_field_manager::get_form_field_from_label($option['Option'], $this);
            $value = $option['Selected'] == 'X' ? 1 : 0;
            $field->set_value($value);

            $fieldnode = $this->find_field($option['Option']);
            $name = $fieldnode->getAttribute('name');
            $field = behat_field_manager::get_form_field_from_label("{$name}_locked", $this);
            $value = $option['Locked'] == 'X' ? 1 : 0;
            $field->set_value($value);
        }
    }

    /**
     * @When /^I select the rollover options? "([^"]*)" +\# local_rollover$/
     */
    public function i_select_the_rollover_option($options) {
        $options = explode(';', $options);

        foreach ($options as $option) {
            if (empty($option)) {
                continue;
            }
            $option = trim($option);
            $field = behat_field_manager::get_form_field_from_label($option, $this);
            $field->set_value(1);
        }
    }

    /**
     * @Given /^I (?:am at|go to) the "([^"]*)" settings page +\# local_rollover$/
     */
    public function i_am_at_the_settings_page($page) {
        if ($page == 'Rollover past instances filter') {
            $url = '/local/rollover/past-instances-filter.php';
        } else {
            $url = '/admin/settings.php?section=local_' . str_replace(' ', '_', strtolower($page));
        }
        $this->visitPath($url);
    }

    /**
     * @Given /^I am rolling over a course at the "([^"]*)" step +\# local_rollover$/
     */
    public function i_am_rolling_over_a_course_at_the_step($step) {
        if ($step != 'Rollover options') {
            throw new \Behat\Behat\Tester\Exception\PendingException();
        }

        $this->generator()->create_course_by_shortname('source')->id;
        $this->generator()->create_course_by_shortname('destination')->id;

        $this->i_go_to_the_rollover_page_for_the_course('destination');
        $this->i_select_in_field('source', 'Original course');
        $this->execute('behat_forms::press_button', ['Next']);
    }

    /**
     * @Then /^I should see the checkbox "([^"]*)" ((?:un)?selected)( and disabled)? +\# local_rollover$/
     */
    public function i_should_see_the_checkbox($checkbox, $selectedornot, $disabled = null) {
        $selected = ($selectedornot == 'selected');
        $disabled = ($disabled != null);
        $element = $this->find_field($checkbox);

        if ($element->isChecked() != $selected) {
            throw new ExpectationException('"' . $checkbox . '" should be ' . $selectedornot, $this->getSession());
        }

        if ($disabled && !$element->getAttribute('disabled')) {
            throw new ExpectationException('"' . $checkbox . '" should be disabled.', $this->getSession());
        }
    }

    /**
     * @Given /^the course "([^"]*)" has a student "([^"]*)" +\# local_rollover$/
     */
    public function the_course_has_a_student($course, $user) {
        $this->generator()->enrol_student($user, $course);
    }

    /**
     * @When /^I ((?:de)?select) "([^"]*)" in the list of activities\/resources +\# local_rollover$/
     */
    public function i_select_in_the_list_of_activities_resources($selectornot, $selections) {
        $selections = explode(';', $selections);

        foreach ($selections as $selection) {
            if (empty($selection)) {
                continue;
            }
            $selection = trim($selection);
            $field = behat_field_manager::get_form_field_from_label($selection, $this);
            $field->set_value($selectornot == 'select' ? '1' : '0');
        }
    }

    /**
     * @Then /^I should (see|not see) the following "([^"]*)" +\# local_rollover$/
     */
    public function i_should_see_the_following($seeornot, $texts) {
        $assertion = $seeornot == 'see' ? 'assert_page_contains_text' : 'assert_page_not_contains_text';
        $texts = explode(';', $texts);

        foreach ($texts as $text) {
            if (empty($text)) {
                continue;
            }
            $this->execute("behat_general::{$assertion}", [$text]);
        }
    }

    /**
     * @Given /^the default rollover settings do not include anything by default +\# local_rollover$/
     */
    public function the_default_rollover_settings_do_not_include_anything_by_default() {
        $options = array_keys(rollover_settings::get_rollover_options_defaults());
        foreach ($options as $option) {
            set_config("option_{$option}", 0, 'local_rollover');
        }
    }

    /**
     * @Given /^the course "([^"]*)" has an HTML block "([^"]*)" +\# local_rollover$/
     */
    public function the_course_has_an_html_block($course, $text) {
        $this->generator()->create_html_block($course, $text);
    }

    /**
     * @Given /^the past instances RegEx is set to "([^"]*)" +\# local_rollover$/
     */
    public function the_past_instances_regex_is_set_to($regex) {
        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, $regex, 'local_rollover');
    }
}
