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

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../lib/phpunit/classes/util.php');

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

    /**
     * @Given /^there is a course with shortname "([^"]*)" +\# local_rollover$/
     */
    public function there_is_a_course_with_shortname($shortname) {
        $generator = phpunit_util::get_data_generator();
        $this->courses[$shortname] = $generator->create_course(['shortname' => $shortname]);
    }

    /**
     * @Given /^I am at the course "([^"]*)" page +\# local_rollover$/
     */
    public function i_am_at_the_course_page($shortname) {
        $this->getSession()->visit($this->locate_path('/course/view.php?name=' . $shortname));
    }

    /**
     * @Given /^I am an administrator +\# local_rollover$/
     */
    public function i_am_an_administrator() {
        $this->execute('behat_auth::i_log_in_as', ['admin']);
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
        $generator = phpunit_util::get_data_generator()->get_plugin_generator('mod_assign');
        $generator->create_instance([
                                        'course' => $this->courses[$shortname]->id,
                                        'name'   => $assignment,
                                    ]);
    }
}
