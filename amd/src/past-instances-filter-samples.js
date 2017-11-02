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
    function PastInstancesFilterSamples() {
        RegExSamples.call(this, ['regex']);
    }

    PastInstancesFilterSamples.prototype = Object.create(RegExSamples.prototype);
    PastInstancesFilterSamples.prototype.constructor = PastInstancesFilterSamples;

    PastInstancesFilterSamples.prototype.getSamplesElement = function (response) {
        if (response.groups.length === 0) {
            return null;
        }

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

        return $rootUL;
    };

    PastInstancesFilterSamples.prototype.updateSamples = function () {
        var args = {regex: document.getElementById('id_regex').value};
        var webservices = [
            {methodname: 'local_rollover_regex_filter_get_sample_matches_by_regex', args: args}
        ];
        $('.local_rollover_samples_spinner').show();
        var promises = ajax.call(webservices);

        var promise = promises[0];
        promise.done(this.samplesReceived.bind(this)).fail(function (response) {
            window.console.error(response);
        });
    };

    return {
        initialise: function () {
            new PastInstancesFilterSamples();
        }
    };
});
