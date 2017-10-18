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
$string['performrollover'] = 'Perform rollover';
$string['pluginname'] = 'Course rollover';
$string['proceed'] = 'Proceed to course';
$string['regex'] = 'Regular Expression';
$string['regex_error_too_short'] = 'RegEx too short';
$string['regex_error_invalid_start'] = 'RegEx must start with /^ (or another delimiter)';
$string['regex_error_invalid_end'] = 'RegEx must end with $/ (or another delimiter)';
$string['regex_error_no_capture'] = 'RegEx must have at least one capture group (parenthesis)';
$string['regex_error_malformed'] = 'Invalid (malformed) RegEx';
$string['rollover'] = 'Rollover';
$string['rolloversuccessfulmessage'] = 'Course <b>{$a->from}</b> rolled over into <b>{$a->into}</b>.';
$string['save'] = 'Save';
$string['settings'] = 'Rollover settings';
$string['settings-options'] = 'Options defaults';
$string['settings-saved'] = 'Settings saved.';
$string['settings-filter'] = 'Past instances filter';
$string['step_activities_and_resources'] = 'Rollover: Select activities and resources';
$string['step_complete'] = 'Rollover successful';
$string['step_content_options'] = 'Rollover: Select content options';
$string['step_source_course'] = 'Rollover: Select source course';
