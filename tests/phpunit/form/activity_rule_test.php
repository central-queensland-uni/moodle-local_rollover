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

use local_rollover\form\form_activity_rule;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

class local_rollover_form_activity_rule_test extends rollover_testcase {
    public function test_it_validates_ok() {
        $errors = $this->get_validation_errors([]);
        self::assertSame([], $errors);
    }

    public function test_it_detects_invalid_regex() {
        $errors = $this->get_validation_errors([form_activity_rule::PARAM_REGEX => '...']);
        self::assertArrayHasKey(form_activity_rule::PARAM_REGEX, $errors);
    }

    private function get_validation_errors($data) {
        $data = array_merge(
            [
                form_activity_rule::PARAM_RULE   => '1_forbid',
                form_activity_rule::PARAM_MODULE => '',
                form_activity_rule::PARAM_REGEX  => '/^regex$/',
            ],
            $data);
        $form = new form_activity_rule();
        $errors = $form->validation($data, []);
        return $errors;
    }
}
