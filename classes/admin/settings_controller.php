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

namespace local_rollover\admin;

use local_rollover\form\form_past_instances_filter;

defined('MOODLE_INTERNAL') || die();

require(__DIR__ . '/../../../../lib/adminlib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_controller {
    const SETTING_PAST_INSTANCES_REGEX = 'past_instances_regex';

    public function __construct() {
        admin_externalpage_setup('local_rollover_filter');
    }

    public function past_instances_settings() {
        global $OUTPUT;

        require_login();

        $form = new form_past_instances_filter();

        echo $OUTPUT->header();
        if ($form->is_saved()) {
            echo $OUTPUT->notification(get_string('settings-saved', 'local_rollover'), 'notifysuccess');
        }
        echo $OUTPUT->heading(get_string('settings-filter', 'local_rollover'));
        $form->display();
        echo $OUTPUT->footer();
    }
}
