@local @local_rollover @javascript
Feature: Ensure capabilities are in effect.
  In order to prevent unauthorized access or rollovers
  As a stakeholder or developer
  I want to ensure that the security checks are in place.

  Scenario: A teacher without the capability cannot see the option to rollover
    Given I am a teacher                                                                 # local_rollover
    And there is a course with shortname "ABC123-2017-2"                                 # local_rollover
    When I go to the course "ABC123-2017-2" page                                         # local_rollover
    Then I should not see "Rollover"

  Scenario: A teacher without the capability cannot access the rollover page
    Given I am a teacher                                                                 # local_rollover
    And there is a course with shortname "ABC123-2017-2"                                 # local_rollover
    When I try to go to the rollover page for the course "ABC123-2017-2"                 # local_rollover
    Then I should see a "nopermissions" exception                                        # local_rollover
