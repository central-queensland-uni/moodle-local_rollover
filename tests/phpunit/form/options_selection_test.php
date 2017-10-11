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

use local_rollover\form\form_options_selection;
use local_rollover\test\rollover_testcase;
use Symfony\Component\DomCrawler\Crawler;

defined('MOODLE_INTERNAL') || die();

class local_rollover_form_options_selection_test extends rollover_testcase {
    public function test_it_uses_defaults_and_locks() {
        self::resetAfterTest(true);
        $options = [
            'option_blocks'       => [false, false],
            'option_users'        => [false, true],
            'option_questionbank' => [true, false],
            'option_activities'   => [true, true],
        ];

        $this->configure_options($options);

        $form = new form_options_selection();

        ob_start();
        $form->display();
        $html = ob_get_clean();

        $this->assert_form_defaults_and_locks($options, $html);
    }

    private function configure_options($options) {
        foreach ($options as $option => $values) {
            list($default, $locked) = $values;
            set_config($option, $default ? 1 : 0, 'local_rollover');
            set_config("{$option}_locked", $locked ? 1 : 0, 'local_rollover');
        }
    }

    private function assert_form_defaults_and_locks($options, $html) {
        $crawler = new Crawler($html);

        foreach ($options as $option => $values) {
            list($default, $locked) = $values;
            $checkbox = $crawler->filter("#id_{$option}")->getNode(0);

            if ($locked && !$default) {
                self::assertNull($checkbox, "{$option} should not appear.");
            } else {
                $default = $default ? 'checked' : '';
                $locked = $locked ? 'disabled' : '';
                self::assertSame($default, $checkbox->getAttribute('checked'), "{$option} default");
                self::assertSame($locked, $checkbox->getAttribute('disabled'), "{$option} lock");
            }
        }
    }
}
