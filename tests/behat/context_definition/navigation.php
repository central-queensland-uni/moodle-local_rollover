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
use local_rollover\local\rollover\rollover_parameters;

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait local_rollover_behat_context_definition_for_navigation {
    /** @var string */
    protected $myusername = null;

    /** @var string */
    protected $lastexception = null;

    /**
     * @Given /^I am an? (administrator|teacher) +\# local_rollover$/
     */
    public function iAmA($user) {
        if ($user == 'administrator') {
            $user = 'admin';
        } else {
            $this->generator()->create_user_by_username($user);
        }
        $this->myusername = $user;

        $this->execute('behat_auth::i_log_in_as', [$user]);
    }

    /**
     * @Given /^I (?:am at|go to) the course "([^"]*)" page +\# local_rollover$/
     */
    public function iGoToTheCoursePage($shortname) {
        $this->visitPath('/course/view.php?name=' . $shortname);
    }

    /**
     * @When /^I press "([^"]*)" in the Course Administration block +\# local_rollover$/
     */
    public function iPressInTheCourseAdministrationBlock($link) {
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
     * @When /^I (try to )?go to the rollover page for the course "([^"]*)" +\# local_rollover$/
     */
    public function iGoToTheRolloverPageForTheCourse($try, $shortname) {
        $try = !empty($try);
        $param = rollover_parameters::PARAM_DESTINATION_COURSE_ID . '=' . $this->generator()->get_course_id($shortname);
        $this->visitPath("/local/rollover/index.php?{$param}");
        $this->lastexception = null;

        if (!$try) {
            return;
        }

        try {
            $this->look_for_exceptions();
        } catch (Exception $exception) {
            $this->lastexception = $exception;
            $this->visitPath("/"); // Clear the exceptions to avoid failing the step.
        }
    }

    /**
     * @Given /^I select "([^"]*)" in "([^"]*)" +\# local_rollover$/
     */
    public function iSelectInField($option, $field) {
        $this->execute('behat_forms::i_set_the_field_to', [$field, $option]);
    }

    /**
     * @Given /^I set the following default options: +\# local_rollover$/
     */
    public function iSetTheFollowingDefaultOptions(TableNode $options) {
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
    public function iSelectTheRolloverOptions($options) {
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
    public function iAmAtTheSettingsPage($page) {
        if ($page == 'Rollover past instances filter') {
            $url = '/local/rollover/past-instances-filter.php';
        } else if ($page == 'Activities & Resources') {
            $url = '/local/rollover/activities-rules.php';
        } else {
            $url = '/admin/settings.php?section=local_' . str_replace(' ', '_', strtolower($page));
        }
        $this->visitPath($url);
    }

    /**
     * @When /^I ((?:de)?select) "([^"]*)" in the list of activities\/resources +\# local_rollover$/
     */
    public function iSelectInTheListOfActivitiesResources($selectornot, $selections) {
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
     * @Given /^I am rolling over a course at the "([^"]*)" step +\# local_rollover$/
     */
    public function iAmRollingOverACourseAtTheSteop($step) {
        if ($step != 'Rollover options') {
            throw new \Behat\Behat\Tester\Exception\PendingException();
        }

        $this->generator()->create_course_by_shortname('source')->id;
        $this->generator()->create_course_by_shortname('destination')->id;

        $this->iGoToTheRolloverPageForTheCourse('', 'destination');
        $this->iSelectInField('source', 'Original course');
        $this->execute('behat_forms::press_button', ['Next']);
    }

    /**
     * @Given /^I set the duration field "([^"]*)" to (\d+) (second|minute|hour|day|week)s? +\# local_rollover$/
     */
    public function iSetTheDurationFieldToDay($field, $number, $unit) {
        $idvalue = $this->find_field($field)->getAttribute('id');
        $idunit = substr($idvalue, 0, -1) . 'u';

        $this->execute('behat_forms::set_field_value', [$field, $number]);
        $this->iSelectInField("{$unit}s", $idunit);
    }

    /**
     * @Given /^I hack the HTML to select "([^"]*)" as the original course +\# local_rollover$/
     */
    public function iHackTheHTMLToSelectAsTheOriginalCourse($original) {
        $session = $this->getSession();
        $id = $this->generator()->get_course_id($original);
        $session->executeScript("document.getElementById('local_rollover-your_units').options[0].value = {$id};");
        $session->executeScript("document.getElementById('local_rollover-your_units').options[0].innerText = '{$original}';");
        $session->executeScript("document.getElementById('local_rollover-your_units').selectedIndex = 0;");
    }

    /**
     * @Given /^I try to press "([^"]*)"$/
     */
    public function iTryToPress($button) {
        $this->lastexception = null;

        $buttonnode = $this->find_button($button);
        if ($this->running_javascript()) {
            $buttonnode->focus();
        }
        $buttonnode->press();

        try {
            $this->look_for_exceptions();
        } catch (Exception $exception) {
            $this->lastexception = $exception;
            $this->visitPath('/'); // Clear the exceptions to avoid failing the step.
        }
    }

    /**
     * @Given /^I hack the HTML so I can continue anyway +\# local_rollover$/
     */
    public function iHackTheHTMLSoICanContinueAnyway() {
        $javascript = <<<JS
document.forms[0].innerHTML += '<input name="submitbutton" value="Continue" type="submit" id="id_submitbutton">';
JS;
        $this->getSession()->executeScript($javascript);
    }

    /**
     * @When /^I view the logs page +\# local_rollover$/
     */
    public function iViewTheLogsPage() {
        $this->visitPath('/report/log/index.php?chooselog=1');
    }

    /**
     * @Given /^I performed a rollover from course "([^"]*)" into "([^"]*)" +\# local_rollover$/
     */
    public function iPerformedARolloverFromCourseInto($source, $destination) {
        $this->generator()->create_course_by_shortname($source);
        $this->generator()->create_course_by_shortname($destination);

        $this->allRolloverProtectionsAreDisabled();
        $this->iGoToTheRolloverPageForTheCourse(false, $destination);
        $this->iSelectInField('ABC123-2017-1', 'Original course');
        $this->execute('behat_forms::press_button', ['Next']);
        $this->execute('behat_forms::press_button', ['Next']);
        $this->execute('behat_forms::press_button', ['Next']);
        $this->execute('behat_forms::press_button', ['Perform rollover']);
    }
}
