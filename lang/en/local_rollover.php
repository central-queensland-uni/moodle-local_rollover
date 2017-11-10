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
 * @var string[] $strings
 */

defined('MOODLE_INTERNAL') || die();

$string['activity_rule_examples'] = 'Example of activities matching rule:';
$string['add_rule_field_rule'] = 'Rule';
$string['add_rule_field_rule_help'] = '<ul>
<li><b>Forbid:</b> Prevents the matching activities from being rolled over.</li>
<li><b>Enforce:</b> Ensures the matching activities are always rolled over.</li>
<li><b>Not Default:</b> Do not rollover the matching activities by default, but they can be manually selected to rollover.</li>
</ul>';
$string['add_rule_field_module'] = 'Activity';
$string['add_rule_field_module_help'] = 'Type of activity or resource to match. Leave it blank for all.';
$string['add_rule_field_regex'] = 'Regular Expression';
$string['add_rule_field_regex_help'] = 'Regular Expression to match the name of the activity. For safety reasons, the regex must be a full match.';
$string['add_new_rule'] = 'Add new rule';
$string['add_rule'] = 'Add rule';
$string['change_rule'] = 'Change rule';
$string['delete-rule-confirmation'] = 'Are you sure you want to remove the following rule?';
$string['no_rules'] = 'No rules found.';
$string['no_matches'] = 'No matches.';
$string['option_activities'] = 'Sets the default for including activities in a rollover.';
$string['option_badges'] = 'Sets the default for including badges in a rollover.';
$string['option_anonymize'] = 'If enabled all information pertaining to users will be anonymised by default.';
$string['option_blocks'] = 'Sets the default for including blocks in a rollover.';
$string['option_comments'] = 'Sets the default for including comments in a rollover.';
$string['option_filters'] = 'Sets the default for including filters in a rollover.';
$string['option_histories'] = 'Sets the default for including user history within a rollover.';
$string['option_logs'] = 'If enabled logs will be included in rollovers by default.';
$string['option_questionbank'] = 'If enabled the question bank will be included in rollovers by default. PLEASE NOTE: Disabling this setting will disable the rollover of activities which use the question bank, such as the quiz.';
$string['option_groups'] = 'Sets the default for including groups and groupings in a rollover.';
$string['option_roleassignments'] = 'If enabled by default roles assignments will also be rolled over.';
$string['option_userscompletion'] = 'If enabled user completion information will be included in rollovers by default.';
$string['option_users'] = 'Sets the default for whether to include users in rollovers.';
$string['originalcourse'] = 'Original course';
$string['originalcourse_help'] = 'Provide the shortname for course to be used as a source for this rollover.';
$string['originalcourse_mycourses'] = 'My courses:';
$string['originalcourse_pastinstances'] = 'Past instances:';
$string['originalcourse_search'] = 'Search';
$string['past_instances_examples'] = 'Examples of past instances';
$string['past_instances_summary_0'] = 'no groups found';
$string['past_instances_summary_1'] = 'only 1 group found';
$string['past_instances_summary_more'] = '{$a} groups found';
$string['performrollover'] = 'Perform rollover';
$string['pluginname'] = 'Course rollover';
$string['precheck_empty'] = 'The destination course already contains activities.';
$string['precheck_hidden'] = 'The destination course is already visible.';
$string['precheck_started'] = 'The destination course has already started.';
$string['precheck_students'] = 'The destination course has enrolled students.';
$string['proceed'] = 'Proceed to course';
$string['regex'] = 'Regular Expression';
$string['regex_error_too_short'] = 'RegEx too short';
$string['regex_error_invalid_start'] = 'RegEx must start with /^ (or another delimiter)';
$string['regex_error_invalid_end'] = 'RegEx must end with $/ plus flags (or another delimiter)';
$string['regex_error_no_capture'] = 'RegEx must have at least one capture group (parenthesis)';
$string['regex_error_malformed'] = 'Invalid (malformed) RegEx';
$string['regex_help'] = '
<b>How to use it?</b>
<ul>
    <li>This filter allows course coordinators to perform rollovers regardless from courses regardless of their capabilities.</li>
    <li>The course coordinator still require editing capabilities for the destination course.</li>
    <li>You can disable this features by leaving this field blank.</li>
    <li>The provided <i>Regular Expression</i> will match against all courses shortnames.</li>
    <li>Any courses that have the same match as in the destination course (capture group) will be selected as a source option for rollover.</li>
</ul>
';
$string['remove_rule'] = 'Remove rule';
$string['rollover'] = 'Rollover';
$string['rolloversuccessfulmessage'] = 'Course <b>{$a->from}</b> rolled over into <b>{$a->into}</b>.';
$string['rule-forbid'] = 'Forbid';
$string['rule-enforce'] = 'Enforce';
$string['rule-not_default'] = 'Not Default';
$string['rule-sentence-1_forbid-all-all'] = 'Forbid rolling over all activities.';
$string['rule-sentence-1_forbid-all-regex'] = 'Forbid rolling over any activity matching:';
$string['rule-sentence-1_forbid-activity-all'] = 'Forbid rolling over all \'{$a->activity}\' activities.';
$string['rule-sentence-1_forbid-activity-regex'] = 'Forbid rolling over any \'{$a->activity}\' matching:';
$string['rule-sentence-2_enforce-all-all'] = 'Enforce rolling over all activities.';
$string['rule-sentence-2_enforce-all-regex'] = 'Enforce rolling over any activity matching:';
$string['rule-sentence-2_enforce-activity-all'] = 'Enforce rolling over all \'{$a->activity}\' activities.';
$string['rule-sentence-2_enforce-activity-regex'] = 'Enforce rolling over any \'{$a->activity}\' matching:';
$string['rule-sentence-3_not_default-all-all'] = 'Do not rollover by default any activity.';
$string['rule-sentence-3_not_default-all-regex'] = 'Do not rollover by default any activity matching:';
$string['rule-sentence-3_not_default-activity-all'] = 'Do not rollover by default any \'{$a->activity}\' activity.';
$string['rule-sentence-3_not_default-activity-regex'] = 'Do not rollover by default any \'{$a->activity}\' matching:';
$string['rule-sentence-number'] = 'Rule #{$a}:';
$string['rule_precedence_disclaimer'] = 'The rollover system will use the first rule that matches, following the precedence order: forbid, enforce and not default.';
$string['rules_match_summary_0'] = 'no activities found';
$string['rules_match_summary_1'] = 'only 1 activity found';
$string['rules_match_summary_plural'] = '{$a} activities found';
$string['rules_match_summary_more'] = '{$a} or more activities found';
$string['save'] = 'Save';
$string['settings'] = 'Rollover settings';
$string['settings-activities'] = 'Activities & Resources';
$string['settings-activities-add-rule-header'] = 'Add rule for activities and resources rollover';
$string['settings-activities-header'] = 'Activities and resources rollover rules';
$string['settings-backup-history'] = 'Backup history';
$string['settings-backup-location'] = 'Path in server';
$string['settings-backup-location-description'] = 'Provide a path in the webserver where to save the rollover backup files.<br /><strong>Notice:</strong> Changing this path will not move any existing data.';
$string['settings-backup-duration'] = 'Retention period';
$string['settings-backup-duration-description'] = 'After the specified time the rollover backup files will be automatically deleted.';
$string['settings-options'] = 'Options defaults';
$string['settings-protection'] = 'Rollover protection';
$string['settings-protection-empty'] = 'If rollover destination is not empty';
$string['settings-protection-empty-description'] = 'Protects against rolling over a course already prepared';
$string['settings-protection-hidden'] = 'If rollover destination is not hidden';
$string['settings-protection-hidden-description'] = 'Protects against rolling over a course that published';
$string['settings-protection-option-stop'] = 'stop and prevent rollover';
$string['settings-protection-option-warn'] = 'show warning';
$string['settings-protection-option-ignore'] = 'do not show warning';
$string['settings-protection-started'] = 'If rollover destination has already started';
$string['settings-protection-started-description'] = 'Protects against rolling over a course that has already started';
$string['settings-protection-students'] = 'If rollover destination contains students';
$string['settings-protection-students-description'] = 'Protects against rolling over a course in use';
$string['settings-saved'] = 'Settings saved.';
$string['settings-filter'] = 'Past instances filter';
$string['step_activities_and_resources'] = 'Rollover: Select activities and resources';
$string['step_complete'] = 'Rollover successful';
$string['step_content_options'] = 'Rollover: Select content options';
$string['step_precheck'] = 'Rollover: Pre-check';
$string['step_source_course'] = 'Rollover: Select source course';
$string['update_rule'] = 'Update rule';
