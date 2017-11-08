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

use local_rollover\form\steps\form_activities_and_resources_selection;
use local_rollover\form\steps\form_options_selection;
use local_rollover\local\rollover\rollover_controller;
use local_rollover\local\rollover\rollover_parameters;
use local_rollover\test\rollover_testcase;
use Symfony\Component\DomCrawler\Crawler;

defined('MOODLE_INTERNAL') || die();

class local_rollover_steps_activities_and_resources_test extends rollover_testcase {
    const ACTITIVIES_STEP_EXPECTATION = '2';

    public function test_it_is_used_after_options_selection_submitted() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $destinationcourse = $this->generator()->create_course_by_shortname('destination');
        $sourcecourse = $this->generator()->create_course_by_shortname('from');

        form_options_selection::mock_submit(
            [
                rollover_parameters::PARAM_CURRENT_STEP          => 1,
                rollover_parameters::PARAM_DESTINATION_COURSE_ID => $destinationcourse->id,
                rollover_parameters::PARAM_SOURCE_COURSE_ID      => $sourcecourse->id,
                'setting_root_activities'                        => 1,
            ]
        );
        $controller = new rollover_controller();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        $crawler = new Crawler($html);

        $formname = str_replace('\\', '_', form_activities_and_resources_selection::class);
        $actual = $crawler->filter("input[name='_qf__{$formname}']")->count();
        self::assertSame(1, $actual, 'Wrong form used.');

        $selector = 'input[name="' . rollover_parameters::PARAM_CURRENT_STEP . '"]';
        $actual = $crawler->filter($selector)->getNode(0)->getAttribute('value');
        self::assertSame(self::ACTITIVIES_STEP_EXPECTATION, $actual);
    }
}
