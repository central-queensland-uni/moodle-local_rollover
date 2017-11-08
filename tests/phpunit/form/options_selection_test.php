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

use local_rollover\backup\backup_worker;
use local_rollover\form\steps\form_options_selection;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\test\rollover_testcase;
use Symfony\Component\DomCrawler\Crawler;

defined('MOODLE_INTERNAL') || die();

class local_rollover_form_options_selection_test extends rollover_testcase {
    public function test_it_uses_defaults_and_locks() {
        self::resetAfterTest(true);
        $options = [
            'blocks'       => [false, false],
            'users'        => [false, true],
            'questionbank' => [true, false],
            'activities'   => [true, true],
        ];

        $this->configure_options($options);

        $source = $this->generator()->create_course();
        $worker = backup_worker::create($source->id);
        $form = new form_options_selection($worker->get_backup_root_settings());

        ob_start();
        $form->display();
        $html = ob_get_clean();

        $this->assert_form_defaults_and_locks($options, $html);
    }

    private function configure_options($options) {
        foreach ($options as $option => $values) {
            list($default, $locked) = $values;
            set_config("option_{$option}", $default ? 1 : 0, 'local_rollover');
            set_config("option_{$option}_locked", $locked ? 1 : 0, 'local_rollover');
        }
    }

    private function assert_form_defaults_and_locks($options, $html) {
        $crawler = new Crawler($html);

        foreach ($options as $option => $values) {
            list($default, $locked) = $values;
            $selector = 'input[name="' . rollover_parameters::PARAM_OPTION_PREFIX . $option . '"]';
            $input = $crawler->filter($selector)->getNode(0);

            if ($locked && !$default) {
                self::assertSame('hidden', $input->getAttribute('type'), "{$option} default");
            } else {
                $default = $default ? 'checked' : '';
                $locked = $locked ? 'disabled' : '';
                self::assertSame($default, $input->getAttribute('checked'), "{$option} default");
                self::assertSame($locked, $input->getAttribute('disabled'), "{$option} lock");
            }
        }
    }
}
