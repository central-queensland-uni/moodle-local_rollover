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
    function RegExSamples(fieldsToMonitor) {
        this.triggerDelayMS = 300;
        this.triggerTimer = null;

        var that = this;
        fieldsToMonitor.forEach(function (fieldToMonitor) {
            $('#id_' + fieldToMonitor).on('input', that.trigger.bind(that));
        });

        this.updateSamples();
    }

    RegExSamples.prototype.trigger = function () {
        if (this.triggerTimer !== null) {
            clearTimeout(this.triggerTimer);
        }
        this.triggerTimer = setTimeout(this.triggered.bind(this), this.triggerDelayMS);
    };

    RegExSamples.prototype.triggered = function () {
        this.triggerTimer = null;
        this.updateSamples();
    };

    RegExSamples.prototype.samplesReceived = function (response) {
        $('.local_rollover_samples_spinner').hide();

        var $error = $('.local_rollover_samples_error');
        $error.find('span').text('');
        $error.hide();

        var $nomatches = $('.local_rollover_samples_no_matches');
        $nomatches.hide();

        var $samples = $('.local_rollover_samples_entries');
        $samples.empty();
        $samples.hide();

        if (response.regexerror !== '') {
            $error.find('span').text(response.regexerror);
            $error.show();
            return;
        }

        var $element = this.getSamplesElement(response);
        if ($element === null) {
            $nomatches.show();
        } else {
            $samples.append($element);
            $samples.show();
        }
    };

    return RegExSamples;
});
