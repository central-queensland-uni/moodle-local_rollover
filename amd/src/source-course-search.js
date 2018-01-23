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
    function SourceCourseSearch() {
        this.delay = 500;

        this.$options = $('#local_rollover-your_units option');
        this.$search = $('#id_search');

        this.timer = null;

        this.cache = [];
        this.cacheOptions();

        this.$search.on('keypress', this.ignoreEnter.bind(this));
        this.$search.on('input', this.trigger.bind(this));
    }

    SourceCourseSearch.prototype.cacheOptions = function () {
        var that = this;
        this.$options.each(function () {
            that.cache.push({
                text: this.innerText.trim().toUpperCase(),
                option: this
            });
        });
    };

    SourceCourseSearch.prototype.ignoreEnter = function (event) {
        var keyCode = event.keyCode || event.which;
        if (keyCode === 13) {
            event.preventDefault();
            return false;
        }
    };

    SourceCourseSearch.prototype.trigger = function () {
        if (this.timer !== null) {
            clearTimeout(this.timer);
        }
        this.timer = setTimeout(this.search.bind(this), this.delay);
    };

    SourceCourseSearch.prototype.search = function () {
        this.timer = null;

        var searching = this.$search.val().trim().toUpperCase();
        if (searching === '') {
            this.showAll();
        } else {
            this.showResults(searching);
        }
    };

    SourceCourseSearch.prototype.showAll = function () {
        this.cache.forEach(function (item) {
            item.option.style.display = '';
        });
    };

    SourceCourseSearch.prototype.showResults = function (searching) {
        this.cache.forEach(function (item) {
            if (item.text.indexOf(searching) === -1) {
                item.option.style.display = 'none';
            } else {
                item.option.style.display = '';
            }
        });
    };

    return new SourceCourseSearch();
});
