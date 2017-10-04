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

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @SuppressWarnings(public) Allow as many methods as needed.
 */
class behat_local_rollover extends behat_base {
    /** @var stdClass[] */
    protected $courses = [];

    /** @var stdClass */
    protected $user = null;

    /**
     * @Given /^there is a course with shortname "([^"]*)" +\# local_rollover$/
     */
    public function there_is_a_course_with_shortname($shortname) {
        $generator = testing_util::get_data_generator();
        $this->courses[$shortname] = $generator->create_course(
            [
                'shortname' => $shortname,
                'fullname'      => "Course {$shortname}",
            ]
        );
    }

    /**
     * @Given /^I am at the course "([^"]*)" page +\# local_rollover$/
     */
    public function i_am_at_the_course_page($shortname) {
        $this->getSession()->visit($this->locate_path('/course/view.php?name=' . $shortname));
    }

    /**
     * @Given /^I am an? (administrator|teacher) +\# local_rollover$/
     */
    public function i_am_an($user) {
        if ($user == 'administrator') {
            $user = 'admin';
        } else {
            $generator = testing_util::get_data_generator();
            $this->user = $generator->create_user([
                                                      'username'  => $user,
                                                      'password'  => $user,
                                                      'firstname' => $user,
                                                      'lastname'  => 'Behat',
                                                  ]);
        }

        $this->execute('behat_auth::i_log_in_as', [$user]);
    }

    /**
     * @When /^I press "([^"]*)" in the Course Administration block +\# local_rollover$/
     */
    public function i_press_in_the_course_administration_block($link) {
        $this->execute('behat_general::i_click_on_in_the', [$link, 'link', 'Administration', 'block']);
    }

    /**
     * @Given /^the course "([^"]*)" has an assignment "([^"]*)" +\# local_rollover$/
     */
    public function the_course_has_an_assignment($shortname, $assignment) {
        $generator = testing_util::get_data_generator()->get_plugin_generator('mod_assign');
        $generator->create_instance([
                                        'course' => $this->courses[$shortname]->id,
                                        'name'   => $assignment,
                                    ]);
    }

    /**
     * @Given /^I (can|cannot) modify the following courses: +\# local_rollover$/
     */
    public function i_modify_the_following_courses($canornot, TableNode $courses) {
        $can = ($canornot == 'can');
        $courses = $courses->getColumn(0);
        $generator = testing_util::get_data_generator();

        foreach ($courses as $course) {
            $this->there_is_a_course_with_shortname($course);
            if ($can) {
                $generator->enrol_user($this->user->id,
                                       $this->courses[$course]->id,
                                       'editingteacher');
            }
        }
    }

    /**
     * @When /^I go to the rollover page for the course "([^"]*)" +\# local_rollover$/
     */
    public function i_go_to_the_rollover_page_for_the_course($shortname) {
        $courseid = $this->courses[$shortname]->id;
        $this->getSession()->visit(
            $this->locate_path('/local/rollover/index.php?into=' . $courseid)
        );
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
     * @Given /^in "([^"]*)" I select "([^"]*)" +\# local_rollover$/
     */
    public function in_field_i_select_option($field, $option) {
        $this->execute('behat_forms::i_set_the_field_to', [$field, $option]);
    }
}
