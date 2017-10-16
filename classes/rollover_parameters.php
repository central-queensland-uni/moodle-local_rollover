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

namespace local_rollover;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rollover_parameters {
    /** Available after any submit, it is used without valiation to determine which form to create. */
    const PARAM_CURRENT_STEP = 'rollover_step';
    /** Available at all steps, it detemines the "current course" and will be used at the restore step. */
    const PARAM_DESTINATION_COURSE_ID = 'rollover_destination_course_id';
    /** Available only when selecting source course, after that it can be retrieved using the backup id. */
    const PARAM_SOURCE_COURSE_ID = 'rollover_source_course_id';
    /** Available after source course is selected. */
    const PARAM_BACKUP_ID = 'rollover_backup_id';
    /** Prefix for content options (linked to backup root task ui names). */
    const PARAM_OPTION_PREFIX = 'setting_root_';
}
