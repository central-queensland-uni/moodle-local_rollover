@local @local_rollover @javascript
Feature: Configure and perform course rollovers.
  In order to test and understand the main features of this plugin
  As a stakeholder or developer
  I want to run the happy path for the main use cases

  These are tests written for stakeholders to test and demonstrate how to use the plugin.


  Scenario: Perform a course rollover
    Given I am an administrator                                                 # local_rollover
    And there is a course with shortname "ABC123-2017-1"                        # local_rollover
    And the course "ABC123-2017-1" has an assignment "Final Exam"               # local_rollover
    And the course "ABC123-2017-1" has a student "john"                         # local_rollover
    And there is a course with shortname "ABC123-2017-2"                        # local_rollover
    And the course "ABC123-2017-2" has a student "jane"                         # local_rollover
    And I am at the course "ABC123-2017-2" page                                 # local_rollover

    When I press "Rollover" in the Course Administration block                  # local_rollover
    Then I should see "Rollover: Select source course"

    When I select "ABC123-2017-1" in "Original course"                          # local_rollover
    And I press "Next"
    Then I should see "Rollover: Select content options"

    When I set the following rollover options:
      |   | Include users                    |
      | X | Include activities and resources |
    And I press "Perform rollover"
    Then I should see "Rollover successful"
    And I should see "ABC123-2017-1 rolled over into ABC123-2017-2"

    When I press "Proceed to course"
    Then I should see "ABC123-2017-2"
    And I should see "Final Exam"

    When I press "Users > Enrolled users" in the Course Administration block    # local_rollover
    Then I should see "jane"
    But I should not see "john"
