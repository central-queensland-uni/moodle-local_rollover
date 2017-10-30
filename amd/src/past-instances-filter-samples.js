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
define(['core/ajax', 'jquery'], function (ajax, $) {
    var TRIGGER_DELAY_MS = 300;

    var trigger, triggered, initialise, updateSamples, samplesReceived;

    var triggerTimer = null;

    initialise = function () {
        $('#id_regex').on('input', trigger);
        updateSamples();
    };

    trigger = function () {
        if (triggerTimer !== null) {
            clearTimeout(triggerTimer);
        }
        triggerTimer = setTimeout(triggered, TRIGGER_DELAY_MS);
    };

    triggered = function () {
        triggerTimer = null;
        updateSamples();
    };

    samplesReceived = function (response) {
        var $regex = $('.local_rollover_filter_regex');
        $regex.text(response.regex);
        $regex.show();

        var $samples = $('.local_rollover_filter_samples');
        $samples.empty();

        var $rootUL = $('<ul>');
        response.groups.forEach(function (group) {
            var $groupLI = $('<li>').text(group.match);
            var $groupUL = $('<ul>');
            $groupLI.append($groupUL);
            group.shortnames.forEach(function (shortname) {
                $groupUL.append($('<li>').text(shortname));
            });
            $rootUL.append($groupLI);
        });

        $samples.append($rootUL);
        $samples.show();

        $('.local_rollover_filter_loading').hide();
    };

    updateSamples = function () {
        var args = {regex: document.getElementById('id_regex').value};
        var webservices = [
            {methodname: 'local_rollover_regex_filter_get_sample_matches_by_regex', args: args}
        ];
        $('.local_rollover_filter_loading').show();
        var promises = ajax.call(webservices);

        var promise = promises[0];
        promise.done(samplesReceived).fail(function (response) {
            window.console.error(response);
        });
    };

    return {
        initialise: initialise
    };
});
