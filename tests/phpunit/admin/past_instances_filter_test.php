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

use local_rollover\admin\settings_controller;
use local_rollover\form\form_past_instances_filter;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_admin_past_instances_filter_test extends rollover_testcase {
    public function test_it_requires_an_admin() {
        $this->resetAfterTest();

        self::setAdminUser();
        $controller = new settings_controller();
        ob_start();
        $controller->past_instances_settings();
        $html = ob_end_clean();
        self::assertNotEmpty($html);

        $user = $this->generator()->create_user();
        self::setUser($user);
        $caught = null;
        try {
            $controller = new settings_controller();
            $controller->past_instances_settings();
        } catch (moodle_exception $exception) {
            $caught = $exception;
        }
        self::assertNotNull($caught);
        self::assertSame('Access denied', $caught->getMessage());
    }

    public function provider_for_test_it_validates_the_regex() {
        return [
            ['', true, 'Empty RegEx is valid.'],
            ['/^(.*)$/', true, 'Capture-all RegEx is valid.'],
            ['.', false, 'RegEx is too short.'],
            ['/(.*)$/', false, 'RegEx must match beggining.'],
            ['/^(.*)/', false, 'RegEx must match end.'],
            ['/^.*$/', false, 'RegEx must have a capture group.'],
            ['/^a(.*b$/', false, 'Malformed RegEx.'],
        ];
    }

    /**
     * @dataProvider provider_for_test_it_validates_the_regex
     */
    public function test_it_validates_the_regex($regex, $acceptable, $reason) {
        $form = new form_past_instances_filter();
        $data = ['regex' => $regex];
        $errors = $form->validation($data, []);

        if ($acceptable) {
            self::assertEmpty($errors, $reason);
        } else {
            self::assertNotEmpty($errors, $reason);
        }
    }
}
