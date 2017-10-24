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

namespace local_rollover\dml;

use moodle_database;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_rule_db {
    /** Database table name. */
    const TABLE = 'local_rollover_activityrules';

    /** Forbidding rules make sure the activity is never rolled over. */
    const RULE_FORBID = '1_forbid';

    /** Enforcing rules make sure the activity is always rolled over. */
    const RULE_ENFORCE = '2_enforce';

    /** Not default rules make the activity not rolled over by default, but they can still be selected. */
    const RULE_NOT_DEFAULT = '3_not_default';

    /** @var moodle_database */
    private $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    public function read($ruleid) {
        $rule = $this->db->get_record(self::TABLE, ['id' => $ruleid]);

        if ($rule === false) {
            return null;
        }

        return $rule;
    }

    public function save($rule) {
        if (isset($rule->id)) {
            $this->update($rule);
        } else {
            $this->create($rule);
        }
    }

    public function all() {
        return $this->db->get_records(self::TABLE, null, 'rule ASC, id ASC');
    }

    private function create($rule) {
        $rule->id = $this->db->insert_record(self::TABLE, $rule);
    }

    private function update($rule) {
        $this->db->update_record(self::TABLE, $rule);
    }
}
