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
define(['jquery'], function ($) {
    return {
        initialise: function () {
            $('#rollover-all-included').on('click', this.selectAll.bind(this));
            $('#rollover-none-included').on('click', this.selectNone.bind(this));
            $('.local_rollover_partial_select a').on('click', this.selectPartial.bind(this));
        },

        getCheckboxes: function () {
            return $('.region-main-container form.mform input[type="checkbox"]:not([disabled])');
        },

        setAll: function (checked) {
            this.getCheckboxes().prop('checked', checked);
        },

        selectAll: function () {
            this.setAll(true);
            return false;
        },

        selectNone: function () {
            this.setAll(false);
            return false;
        },

        setPartial: function (module, selected) {
            module += '_';
            var prefix = 'id_setting_activity_';
            var checkboxes = this.getCheckboxes();
            checkboxes.each(function () {
                var id = this.id;
                if (!id.startsWith(prefix)) {
                    return;
                }
                id = id.substring(prefix.length);
                if (!id.startsWith(module)) {
                    return;
                }
                this.checked = selected;
            });
        },

        selectPartial: function (event) {
            var id = event.currentTarget.id;
            var parts = id.split('-');
            var module = parts[1];
            var selected = (parts[2] === 'all');
            this.setPartial(module, selected);
            return false;
        }
    };
});
