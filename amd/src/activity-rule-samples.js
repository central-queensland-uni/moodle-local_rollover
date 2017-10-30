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
define(['./regex-samples', 'core/ajax', 'jquery'], function (RegExSamples, ajax, $) {
    function ActivityRuleSamples() {
        RegExSamples.call(this, ['module', 'regex']);
    }

    ActivityRuleSamples.prototype = Object.create(RegExSamples.prototype);
    ActivityRuleSamples.prototype.constructor = ActivityRuleSamples;

    ActivityRuleSamples.prototype.getSamplesElement = function (response) {
        var $rootUL = $('<ul>');
        response.activities.forEach(function (activity) {
            var $li = $('<li>');
            $li.text(activity);
            $rootUL.append($li);
        });

        return $rootUL;
    };

    ActivityRuleSamples.prototype.updateSamples = function () {
        $('.local_rollover_samples_loading').show();
        var that = this;
        setTimeout(function () {
            that.samplesReceived({
                activities: [
                    'Activity 1',
                    'Activity 2'
                ]
            });
        }, 1000);
    };

    return {
        initialise: function () {
            new ActivityRuleSamples();
        }
    };
});
