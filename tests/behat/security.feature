@local @local_rollover @javascript
Feature: Ensure capabilities are in effect.
  In order to prevent unauthorized access or rollovers
  As a not-very-nice user
  I want to ensure my attempts to hack the page are not successful

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

  Scenario: A teacher without the capability cannot hack and rollover an unauthorized course
    Given I am a teacher                                                                 # local_rollover
    And all rollover protections are disabled                                            # local_rollover
    And I can modify the the course "ABC123-2017-1"                                      # local_rollover
    And I can modify the the course "ABC123-2017-2"                                      # local_rollover
    And I cannot modify the the course "DEF456-2017-1"                                   # local_rollover
    When I go to the rollover page for the course "ABC123-2017-2"                        # local_rollover
    And I hack the HTML to select "DEF456-2017-1" as the original course                 # local_rollover
    And I try to press "Next"
    Then I should see the "Missing source course id" exception                          # local_rollover

