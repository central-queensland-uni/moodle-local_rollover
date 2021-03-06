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

namespace local_rollover\test;

use advanced_testcase;
use external_api;

defined('MOODLE_INTERNAL') || die();

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren) - All our tests inherit from this one, just put them in childcare.
 */
class rollover_testcase extends advanced_testcase {
    private static function is_rollover_event($event) {
        $match = 'local_rollover\\';
        $class = get_class($event);
        $found = substr($class, 0, strlen($match));
        return ($found === $match);
    }

    public static function filter_rollover_events($events) {
        $result = [];

        foreach ($events as $event) {
            if (self::is_rollover_event($event)) {
                $result[] = $event;
            }
        }

        return $result;
    }

    /** @var generator */
    private $generator = null;

    /**
     * @return generator
     */
    public function generator() {
        if (is_null($this->generator)) {
            $this->generator = new generator();
        }
        return $this->generator;
    }

    /**
     * @param $methodname
     * @param $args
     * @return mixed
     */
    public function call_webservice_successfully($methodname, $args) {
        require_once(__DIR__ . '/../../../../lib/externallib.php');

        $_GET['sesskey'] = sesskey();
        $response = external_api::call_external_function($methodname,
                                                         $args, true);

        if ($response['error'] !== false) {
            self::fail('WebService call must not return an error, got: ' . $response['exception']->message);
        }

        return $response['data'];
    }
}
