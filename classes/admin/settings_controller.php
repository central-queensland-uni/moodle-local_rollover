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

use html_writer;
use local_rollover\dml\activity_rule_db;
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

    public static function create_rule_sentence($number, $rule) {
        global $DB;

        $rulenumber = html_writer::tag('b',
                                       get_string('rule-sentence-number', 'local_rollover', $number));

        $data = ['regex' => $rule->regex];
        if (!is_null($rule->moduleid)) {
            $name = $DB->get_field('modules', 'name', ['id' => $rule->moduleid], MUST_EXIST);
            $data['activity'] = get_string('modulename', $name);
        }

        $lang = "rule-sentence-{$rule->rule}";
        $lang .= is_null($rule->moduleid) ? '-all' : '-activity';
        $lang .= empty($rule->regex) ? '-all' : '-regex';
        $lang = get_string($lang, 'local_rollover', $data);

        return "{$rulenumber} {$lang}";
    }

    public function __construct() {
        admin_externalpage_setup('local_rollover_filter');
        require_login();
    }

    public function past_instances_settings() {
        global $OUTPUT, $PAGE;

        $form = new form_past_instances_filter();

        $PAGE->requires->js_call_amd('local_rollover/past-instances-filter-samples', 'initialise');

        echo $OUTPUT->header();
        if ($form->is_saved()) {
            echo $OUTPUT->notification(get_string('settings-saved', 'local_rollover'), 'notifysuccess');
        }
        echo $OUTPUT->heading(get_string('settings-filter', 'local_rollover'));
        $form->display();
        echo '<section id="local_rollover_filter_samples"></section>';
        echo $OUTPUT->footer();
    }

    public function activities_rules() {
        global $OUTPUT;

        $db = new activity_rule_db();

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('settings-activities-header', 'local_rollover'));

        $rules = $db->get_all();
        if (count($rules) == 0) {
            echo 'No rules active.';
        } else {
            echo html_writer::start_tag('ul');
            foreach (array_values($rules) as $index => $rule) {
                echo html_writer::tag('li', $this->create_rule_sentence($index + 1, $rule));
            }
            echo html_writer::end_tag('ul');
        }
        echo $OUTPUT->footer();
    }
}
