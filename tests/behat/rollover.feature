@local @local_rollover @javascript
Feature: Configure and perform course rollovers.
  In order to test and understand the main features of this plugin
  As a stakeholder or developer
  I want to run the happy path for the main use cases

  These are tests written for stakeholders to test and demonstrate how to use the plugin.


  Scenario: Perform a course rollover
    Given I am a teacher                                          # local_rollover
    And I can modify the following courses:                       # local_rollover
      | ABC123-2017-1   |
      | ABC123-2017-2   |
    And the course "ABC123-2017-1" has an assignment "Final Exam" # local_rollover
    And I am at the course "ABC123-2017-2" page                   # local_rollover

    When I press "Rollover" in the Course Administration block    # local_rollover
    And in "Original course" I select "ABC123-2017-1"             # local_rollover
    And I press "Perform rollover"
    Then I should see "Rollover successful"
    And I should see "ABC123-2017-1 rolled over into ABC123-2017-2"

    When I press "Proceed to course"
    Then I should see "ABC123-2017-2"
    And I should see "Final Exam"
