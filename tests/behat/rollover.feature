@local @local_rollover @javascript
Feature: Perform course rollover

  Scenario: Simple course rollover
    Given there is a course with shortname "ABC123-2017-1"        # local_rollover
    And there is a course with shortname "ABC123-2017-2"          # local_rollover
    And I am an administrator                                     # local_rollover
    And I am at the course "ABC123-2017-2" page                   # local_rollover
    When I press "Rollover" in the Course Administration block    # local_rollover
    And I set the field "Original course" to "ABC123-2017-1"
    And I press "Perform rollover"
    Then I should see "Rollover successful"
    And I should see "ABC123-2017-1 rolled over into ABC123-2017-2"

