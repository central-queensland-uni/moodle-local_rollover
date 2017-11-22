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
 * @codingStandardsIgnoreFile
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait local_rollover_behat_context_definition_for_assertions {
    /**
     * @Then /^I (should(?: not)?) see the following source options: +\# local_rollover$/
     */
    public function iShouldSeeTheFollowingSourceOptions($shouldornot, TableNode $courses) {
        $courses = $courses->getColumn(0);
        $shouldnot = ($shouldornot == 'should') ? '' : 'not_';
        $context = "behat_general::assert_element_{$shouldnot}contains_text";

        foreach ($courses as $course) {
            $this->execute(
                $context,
                [$course, '#local_rollover-your_units', 'css_element']
            );
        }
    }

    /**
     * @Then /^I should see the checkbox "([^"]*)" ((?:un)?selected)( and disabled)? +\# local_rollover$/
     */
    public function iShouldSeeTheCheckbox($checkbox, $selectedornot, $disabled = null) {
        $selected = ($selectedornot == 'selected');
        $disabled = ($disabled != null);
        $element = $this->find_field($checkbox);

        if ($element->isChecked() != $selected) {
            throw new ExpectationException('"' . $checkbox . '" should be ' . $selectedornot, $this->getSession());
        }

        if ($disabled && !$element->getAttribute('disabled')) {
            throw new ExpectationException('"' . $checkbox . '" should be disabled.', $this->getSession());
        }
    }

    /**
     * @Then /^I should (see|not see) the following "([^"]*)" +\# local_rollover$/
     */
    public function iShouldSeeTheFollowing($seeornot, $texts) {
        $assertion = $seeornot == 'see' ? 'assert_page_contains_text' : 'assert_page_not_contains_text';
        $texts = explode(';', $texts);

        foreach ($texts as $text) {
            if (empty($text)) {
                continue;
            }
            $this->execute("behat_general::{$assertion}", [$text]);
        }
    }

    /**
     * @Then /^the activities options should be: +\# local_rollover$/
     */
    public function theActivitiesOptionsShouldBe(TableNode $foundactivities) {
        foreach ($foundactivities->getColumnsHash() as $activity) {
            $name = $activity['activity'];
            $expectselected = ($activity['selected'] == 'yes');
            $expectmodifiable = ($activity['modifiable'] == 'yes');

            if ($expectmodifiable) {
                $ischecked = $this->is_modifiable_activity_selected($name);
            } else {
                $ischecked = $this->is_not_modifiable_activity_selected($name);
            }

            if ($ischecked != $expectselected) {
                throw new ExpectationException('"' . $name . '" should be selected: ' . $activity['selected'],
                                               $this->getSession());
            }
        }
    }

    private function is_modifiable_activity_selected($name) {
        $element = $this->find_field($name);
        $ischecked = $element->isChecked();
        return $ischecked;
    }

    /**
     * @Given /^I should (see|not see) the button "([^"]*)"                                             \# local_rollover$/
     */
    public function iShouldTheButton($seeornot, $button) {
        $found = null;
        try {
            $found = $this->find_button($button);
        } catch (ElementNotFoundException $e) {
        }

        $seeornot = ($seeornot == 'see');
        $found = !is_null($found);
        if ($seeornot != $found) {
            $found = $found ? 'found' : 'not found';
            throw new ExpectationException("Button '{$button}' {$found}.", $this->getSession());
        }
    }

    private function is_not_modifiable_activity_selected($name) {
        /** @var NodeElement[] $foundactivities */
        $foundactivities = $this->find_all('css', '.grouped_settings.activity_level');

        foreach ($foundactivities as $foundactivity) {
            $label = $foundactivity->find('css', '.fstaticlabel');
            if (is_null($label)) {
                continue;
            }

            $label = trim($label->getText());
            if ($label == $name) {
                $img = $foundactivity->find('css', 'img.smallicon');
                $foundselected = strtolower($img->getAttribute('title'));
                return ($foundselected == 'yes');
            }
        }

        throw new ExpectationException('Static "' . $name . '" not found. Maybe it is modifiable.', $this->getSession());
    }

    /**
     * @Then /^I should see (?:a|the) "([^"]*)" exception +\# local_rollover$/
     */
    public function iShouldSeeAException($expected) {
        $exception = empty($this->lastexception) ? '' : $this->lastexception;
        $actual = empty($exception) ? '' : $exception->getMessage();
        if (strpos($actual, $expected) === false) {
            throw new ExpectationException("Exception '{$expected}' not found.", $this->getSession());
        }
    }
}
