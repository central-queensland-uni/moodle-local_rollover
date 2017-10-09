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
 * @var admin_root $ADMIN
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/adminlib.php');

$category = new admin_category('local_rollover',
                               new lang_string('settings', 'local_rollover')
);
$ADMIN->add('courses', $category);


$options = new admin_settingpage('local_rollover_options',
                                 new lang_string('settings-options', 'local_rollover')
);

$items = [
    ['name' => 'users', 'visiblename' => 'generalusers', 'default' => 1, 'locked' => 0],
    ['name' => 'anonymize', 'visiblename' => 'generalanonymize', 'default' => 0, 'locked' => 0],
    ['name' => 'role_assignments', 'visiblename' => 'generalroleassignments', 'default' => 1, 'locked' => 0],
    ['name' => 'activities', 'visiblename' => 'generalactivities', 'default' => 1, 'locked' => 0],
    ['name' => 'blocks', 'visiblename' => 'generalblocks', 'default' => 1, 'locked' => 0],
    ['name' => 'filters', 'visiblename' => 'generalfilters', 'default' => 1, 'locked' => 0],
    ['name' => 'comments', 'visiblename' => 'generalcomments', 'default' => 1, 'locked' => 0],
    ['name' => 'badges', 'visiblename' => 'generalbadges', 'default' => 1, 'locked' => 0],
    ['name' => 'userscompletion', 'visiblename' => 'generaluserscompletion', 'default' => 1, 'locked' => 0],
    ['name' => 'logs', 'visiblename' => 'generallogs', 'default' => 0, 'locked' => 0],
    ['name' => 'histories', 'visiblename' => 'generalhistories', 'default' => 0, 'locked' => 0],
    ['name' => 'questionbank', 'visiblename' => 'generalquestionbank', 'default' => 1, 'locked' => 0],
    ['name' => 'groups', 'visiblename' => 'generalgroups', 'default' => 1, 'locked' => 0],
];
foreach ($items as $item) {
    $langname = str_replace('_', '', $item['name']);
    $options->add(
        new admin_setting_configcheckbox_with_lock('rollover/option_' . $item['name'],
                                                   new lang_string("general{$langname}", 'backup'),
                                                   new lang_string("option_{$langname}", 'local_rollover'),
                                                   ['value' => $item['default'], 'locked' => $item['locked']])
    );
}


$ADMIN->add('local_rollover', $options);
