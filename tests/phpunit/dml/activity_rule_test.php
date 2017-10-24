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

use local_rollover\dml\activity_rule;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_dml_activity_rule_test extends rollover_testcase {
    /** @var activity_rule */
    private $dml;

    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->dml = new activity_rule();
    }

    public function test_it_can_create() {
        $this->resetAfterTest();

        $rule = (object)[
            'rule'     => 'forbid',
            'moduleid' => null,
            'regex'    => '',
        ];

        $this->dml->create($rule);

        self::assertNotEmpty($rule->id);
    }
}
