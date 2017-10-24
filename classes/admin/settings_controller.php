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
use local_rollover\form\form_activity_rule;
use local_rollover\form\form_activity_rule_delete;
use local_rollover\form\form_past_instances_filter;
use moodle_url;
use stdClass;

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
        require_login();
    }

    public function past_instances_settings() {
        global $OUTPUT, $PAGE;

        admin_externalpage_setup('local_rollover_filter');

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
        admin_externalpage_setup('local_rollover_activities');

        $action = optional_param(form_activity_rule::PARAM_ACTION, '', PARAM_ALPHANUM);
        switch ($action) {
            case 'add':
                $this->activities_rules_add();
                break;
            case 'edit':
                $this->activities_rules_edit();
                break;
            case 'delete':
                $this->activities_rules_delete();
                break;
            default:
                $this->activities_rules_view();
                break;
        }
    }

    private function activities_rules_view() {
        global $OUTPUT;

        $db = new activity_rule_db();

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('settings-activities-header', 'local_rollover'));

        $rules = $db->all();
        if (count($rules) == 0) {
            echo html_writer::tag('p', get_string('no_rules', 'local_rollover'));
        } else {
            echo html_writer::start_tag('ul');
            foreach (array_values($rules) as $index => $rule) {
                $rulenumber = $index + 1;
                $text = $this->create_rule_sentence($rulenumber, $rule);
                $change = new moodle_url('/local/rollover/activities-rules.php',
                                         [
                                             form_activity_rule::PARAM_ACTION => 'edit',
                                             form_activity_rule::PARAM_RULEID => $rule->id,
                                         ]);
                $change = html_writer::link($change, get_string('change_rule', 'local_rollover'));
                $remove = new moodle_url('/local/rollover/activities-rules.php',
                                         [
                                             'rulenumber'                     => $rulenumber,
                                             form_activity_rule::PARAM_ACTION => 'delete',
                                             form_activity_rule::PARAM_RULEID => $rule->id,
                                         ]);
                $remove = html_writer::link($remove, get_string('remove_rule', 'local_rollover'));
                echo html_writer::tag('li', "{$text} [ {$change} | {$remove} ]");
            }
            echo html_writer::end_tag('ul');
        }

        echo html_writer::link(new moodle_url('/local/rollover/activities-rules.php', [form_activity_rule::PARAM_ACTION => 'add']),
                               get_string('add_new_rule', 'local_rollover'));

        echo $OUTPUT->footer();
    }

    private function activities_rules_add() {
        $this->activities_rules_add_or_edit(null);
    }

    private function activities_rules_save($rule, $data) {
        $dml = new activity_rule_db();

        if (is_null($rule)) {
            $rule = new stdClass();
        }
        $rule->rule = $data->{form_activity_rule::PARAM_RULE};
        $rule->moduleid = $data->{form_activity_rule::PARAM_MODULE};
        $rule->regex = $data->{form_activity_rule::PARAM_REGEX};

        $dml->save($rule);
    }

    private function activities_rules_edit() {
        $ruleid = required_param(form_activity_rule::PARAM_RULEID, PARAM_INT);
        $dml = new activity_rule_db();
        $rule = $dml->read($ruleid);
        $this->activities_rules_add_or_edit($rule);
    }

    private function activities_rules_add_or_edit($rule) {
        global $OUTPUT;

        $form = new form_activity_rule();

        if ($form->is_cancelled()) {
            $this->activities_rules_view();
            return;
        }

        $data = $form->get_data();
        if ($data) {
            $this->activities_rules_save($rule, $data);
            $this->activities_rules_view();
            return;
        }

        if (!is_null($rule)) {
            $form->set_data([
                                form_activity_rule::PARAM_ACTION => 'edit',
                                form_activity_rule::PARAM_RULEID => $rule->id,
                                form_activity_rule::PARAM_RULE   => $rule->rule,
                                form_activity_rule::PARAM_MODULE => $rule->moduleid,
                                form_activity_rule::PARAM_REGEX  => $rule->regex,
                            ]);
        }

        echo $OUTPUT->header();

        echo $OUTPUT->heading(get_string('settings-activities-add-rule-header', 'local_rollover'));

        $form->display();

        echo $OUTPUT->footer();
    }

    private function activities_rules_delete() {
        global $OUTPUT;
        $dml = new activity_rule_db();
        $ruleid = required_param(form_activity_rule::PARAM_RULEID, PARAM_INT);
        $form = new form_activity_rule_delete();

        if ($form->is_cancelled()) {
            $this->activities_rules_view();
            return;
        }

        if ($form->is_submitted()) {
            $dml->delete($ruleid);
            $this->activities_rules_view();
            return;
        }
        $rule = $dml->read($ruleid);

        echo $OUTPUT->header();

        echo $OUTPUT->heading(get_string('settings-activities-add-rule-header', 'local_rollover'));

        echo html_writer::tag('b', get_string('delete-rule-confirmation', 'local_rollover'));

        $number = required_param('rulenumber', PARAM_INT);
        echo html_writer::tag('p', static::create_rule_sentence($number, $rule));

        $form->display();

        echo $OUTPUT->footer();
    }
}
