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

use block_manager;
use context_course;
use local_rollover\dml\activity_rule_db;
use local_rollover\local\protection;
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

    public function create_course_by_shortname($shortname, $options = []) {
        $data = array_merge($options, ['shortname' => $shortname]);
        $this->courses[$shortname] = $this->create_course($data);
        return $this->courses[$shortname];
    }

    public function create_user_by_username($username) {
        if ($username === 'admin') {
            $user = get_admin();
        } else {
            $user = $this->create_user([
                                           'username'  => $username,
                                           'password'  => $username,
                                           'firstname' => $username,
                                           'lastname'  => 'Behat',
                                       ]);
        }

        $this->users[$username] = $user;
        return $this->users[$username];
    }

    public function create_activity($course, $activity, $name) {
        if (!array_key_exists($course, $this->courses)) {
            $this->create_course_by_shortname($course);
        }

        if ($activity == 'assignment') {
            $activity = 'assign';
        }

        return $this->get_plugin_generator("mod_{$activity}")->create_instance(
            [
                'course' => $this->courses[$course]->id,
                'name'   => $name,
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

    public function create_html_block($course, $text) {
        global $CFG, $DB, $PAGE;
        require_once($CFG->libdir . '/blocklib.php');

        // Add block relies on the current context, so we fake it...
        $oldcontext = $PAGE->context;
        $PAGE->set_context(context_course::instance($this->get_course_id($course)));

        $manager = new block_manager($PAGE);
        $manager->add_region('side-pre');
        $manager->add_block('html', 'side-pre', 0, false,
                            'course-view-*', null);

        // Why add_block above does not return an id? Oh no, hacky code below...
        $block = $DB->get_records('block_instances', null, 'id DESC', '*', 0, 1);
        $block = array_pop($block);

        // Ouch, we did it... more hacky code! Set it back and hope for the best...
        $PAGE->set_context($oldcontext);

        // Set HTML data.
        $configdata = (object)[
            'text'   => "<p>{$text}</p>",
            'title'  => $text,
            'format' => '1',
        ];
        $block->configdata = base64_encode(serialize($configdata));
        $DB->update_record('block_instances', $block);

        return $block;
    }

    public function create_activity_rule($rule, $module, $regex) {
        global $DB;

        $moduleid = null;
        if (!empty($module)) {
            if ($module == 'assignment') {
                $module = 'assign';
            }
            $moduleid = $DB->get_field('modules', 'id', ['name' => $module], MUST_EXIST);
        }

        $dml = new activity_rule_db();
        $rule = (object)[
            'rule'     => $rule,
            'moduleid' => $moduleid,
            'regex'    => $regex,
        ];

        $dml->save($rule);

        return $rule;
    }

    public function disable_protection() {
        protection::set_config(protection::PROTECT_HAS_STARTED, protection::ACTION_IGNORE);
        protection::set_config(protection::PROTECT_NOT_HIDDEN, protection::ACTION_IGNORE);
        protection::set_config(protection::PROTECT_HAS_STUDENTS, protection::ACTION_IGNORE);
        protection::set_config(protection::PROTECT_NOT_EMPTY, protection::ACTION_IGNORE);
    }
}
