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

namespace local_rollover\event;

use core\event\base;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rollover_completed extends base {
    /**
     * Returns localised general event name.
     *
     * Override in subclass, we can not make it static and abstract at the same time.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_rollover_completed', 'local_rollover');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        $source = $this->get_source_course_id();
        $destination = $this->get_destination_course_id();
        $filename = $this->get_filename();
        return "The user with the id '{$this->userid}' completed rollover from course id '{$source}' into '{$destination}'" .
               " with backup file: {$filename}";
    }

    /**
     * Returns relevant URL, override in subclasses.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url('/course/view.php', ['id' => $this->get_destination_course_id()]);
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    public function get_destination_course_id() {
        return $this->data['objectid'];
    }

    public function get_source_course_id() {
        return $this->data['other']['sourceid'];
    }

    public function get_backup_id() {
        return $this->data['other']['backupid'];
    }

    public function get_filename() {
        return $this->data['other']['filename'];
    }
}
