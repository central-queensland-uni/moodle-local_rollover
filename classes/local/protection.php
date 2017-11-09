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

namespace local_rollover\local;

use local_rollover\admin\rollover_settings;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class protection {
    const PROTECT_NOT_EMPTY = 'empty';

    const PROTECT_NOT_HIDDEN = 'hidden';

    const PROTECT_HAS_USER_DATA = 'user';

    const PROTECT_HAS_STARTED = 'started';

    const ACTION_STOP = 'stop';

    const ACTION_WARN = 'warn';

    const ACTION_IGNORE = 'ignore';

    public static function get_actions() {
        return [self::ACTION_STOP, rollover_settings::DEFAULT_PROTECTION_LEVEL, self::ACTION_IGNORE];
    }

    public static function get_protections() {
        return [
            self::PROTECT_NOT_EMPTY,
            self::PROTECT_NOT_HIDDEN,
            self::PROTECT_HAS_USER_DATA,
            self::PROTECT_HAS_STARTED,
        ];
    }

    public static function get_config_key($protection) {
        return "protection_{$protection}";
    }

    public static function get_config($protection) {
        $key = self::get_config_key($protection);
        $value = get_config('local_rollover', $key);
        if ($value === false) {
            $value = rollover_settings::DEFAULT_PROTECTION_LEVEL;
        }
        return $value;
    }

    public static function set_config($protection, $value) {
        $key = self::get_config_key($protection);
        set_config($key, $value, 'local_rollover');
    }

    /** @var string[] */
    private $actions;

    /** @var stdClass */
    private $course;

    public function __construct($course) {
        $this->course = $course;
        $this->actions = [];

        $protections = self::get_protections();
        foreach ($protections as $protection) {
            $method = "check_{$protection}";
            if (!method_exists($this, $method)) {
                throw new moodle_exception("Missing protection check method: {$method}");
            }
            $this->actions[$protection] = $this->$method();
        }
    }

    public function get_warnings() {
        return $this->find_actions(self::ACTION_WARN);
    }

    public function get_errors() {
        return $this->find_actions(self::ACTION_STOP);
    }

    public function find_actions($needle) {
        $actions = [];
        foreach ($this->actions as $protection => $action) {
            if ($action == $needle) {
                $actions[] = $protection;
            }
        }
        return $actions;
    }

    public function all_ignored() {
        foreach ($this->actions as $action) {
            if ($action != self::ACTION_IGNORE) {
                return false;
            }
        }

        return true;
    }

    public function check_empty() {
        $action = self::get_config(self::PROTECT_NOT_EMPTY);
        if ($action == self::ACTION_IGNORE) {
            return self::ACTION_IGNORE;
        }

        $info = get_fast_modinfo($this->course, -1);
        $instances = $info->get_instances();
        if (count($instances) == 0) {
            return self::ACTION_IGNORE;
        }

        return $action;
    }

    public function check_hidden() {
        $action = self::get_config(self::PROTECT_NOT_HIDDEN);
        if ($action == self::ACTION_IGNORE) {
            return self::ACTION_IGNORE;
        }

        if (!$this->course->visible) {
            return self::ACTION_IGNORE;
        }

        return $action;
    }

    public function check_user() {
        return self::ACTION_IGNORE;
    }

    public function check_started() {
        $action = self::get_config(self::PROTECT_HAS_STARTED);
        if ($action == self::ACTION_IGNORE) {
            return self::ACTION_IGNORE;
        }

        if ($this->course->startdate > time()) {
            return self::ACTION_IGNORE;
        }

        return $action;
    }
}
