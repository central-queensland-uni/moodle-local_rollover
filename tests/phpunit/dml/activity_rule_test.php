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

use local_rollover\dml\activity_rule_db;
use local_rollover\test\rollover_testcase;

defined('MOODLE_INTERNAL') || die();

class local_rollover_dml_activity_rule_test extends rollover_testcase {
    /** @var activity_rule_db */
    private $dml;

    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->dml = new activity_rule_db();
    }

    public function test_it_can_create() {
        $rule = (object)[
            'rule'     => activity_rule_db::RULE_FORBID,
            'moduleid' => null,
            'regex'    => '',
        ];

        $this->dml->save($rule);

        self::assertNotEmpty($rule->id);
    }

    public function test_it_can_read() {
        $actual = $this->dml->read(1);
        self::assertNull($actual);

        $expected = (object)[
            'rule'     => activity_rule_db::RULE_FORBID,
            'moduleid' => null,
            'regex'    => '',
        ];
        $this->dml->save($expected);

        $actual = $this->dml->read($expected->id);
        self::assertEquals((array)$expected, (array)$actual);
    }

    public function test_it_can_update() {
        $expected = (object)[
            'rule'     => activity_rule_db::RULE_FORBID,
            'moduleid' => null,
            'regex'    => '',
        ];
        $this->dml->save($expected);
        $firstid = $expected->id;

        $expected->rule = activity_rule_db::RULE_ENFORCE;
        $this->dml->save($expected);
        $secondid = $expected->id;

        self::assertSame($firstid, $secondid);

        $actual = $this->dml->read($expected->id);
        self::assertEquals((array)$expected, (array)$actual);
    }

    public function test_it_can_delete() {
        $expected = (object)[
            'rule'     => activity_rule_db::RULE_FORBID,
            'moduleid' => null,
            'regex'    => '',
        ];
        $this->dml->save($expected);
        $this->dml->delete($expected->id);

        $actual = $this->dml->read($expected->id);
        self::assertNull($actual);
    }

    public function test_it_gets_all_in_correct_order() {
        global $DB;
        $enforce = $DB->insert_record(activity_rule_db::TABLE,
                                      (object)['rule' => activity_rule_db::RULE_ENFORCE]);
        $forbid = $DB->insert_record(activity_rule_db::TABLE,
                                     (object)['rule' => activity_rule_db::RULE_FORBID]);
        $notdefault = $DB->insert_record(activity_rule_db::TABLE,
                                         (object)['rule' => activity_rule_db::RULE_NOT_DEFAULT, 'moduleid' => null, 'regex' => '']);

        $all = $this->dml->all();
        $ids = array_keys($all);
        self::assertSame([$forbid, $enforce, $notdefault], $ids);
    }

    public function test_it_saves_properly_to_all_modules_if_module_is_missing() {
        $rule = (object)[
            'rule'     => activity_rule_db::RULE_FORBID,
            'moduleid' => '',
            'regex'    => '',
        ];
        $this->dml->save($rule);

        self::assertNull($this->dml->read($rule->id)->moduleid);

        $rule->moduleid = 0;
        self::assertNull($this->dml->read($rule->id)->moduleid);
    }
}
