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

namespace local_rollover\test;

use stdClass;
use testing_data_generator;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../lib/testing/generator/lib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generator extends testing_data_generator {
    /** @var stdClass[] */
    protected $courses = [];

    /** @var stdClass[] */
    protected $users = [];

    public function get_course_id($shortname) {
        return $this->courses[$shortname]->id;
    }

    public function create_course_by_shortname($shortname) {
        $this->courses[$shortname] = $this->create_course(['shortname' => $shortname]);
        return $this->courses[$shortname];
    }

    public function create_user_by_username($username) {
        $this->users[$username] = $this->create_user([
                                                         'username'  => $username,
                                                         'password'  => $username,
                                                         'firstname' => $username,
                                                         'lastname'  => 'Behat',
                                                     ]);
        return $this->users[$username];
    }

    public function create_assignment($course, $assignmentname) {
        return $this->get_plugin_generator('mod_assign')->create_instance(
            [
                'course' => $this->courses[$course]->id,
                'name'   => $assignmentname,
            ]
        );
    }

    public function enrol_editing_teacher($user, $course) {
        $this->enrol_user_role($user, $course, 'editingteacher');
    }

    public function enrol_nonediting_teacher($user, $course) {
        $this->enrol_user_role($user, $course, 'teacher');
    }

    public function enrol_student($user, $course) {
        $this->enrol_user_role($user, $course, 'student');
    }

    private function enrol_user_role($user, $course, $role) {
        if (!array_key_exists($user, $this->users)) {
            $this->create_user_by_username($user);
        }
        $this->enrol_user(
            $this->users[$user]->id,
            $this->courses[$course]->id,
            $role
        );
    }
}
