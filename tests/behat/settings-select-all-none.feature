@local @local_rollover @javascript
Feature: Allow hiding the select all/none section when selecting activities
  In order to choose between a simple UI or advanced options
  As an administrator
  I want to be able to enable or disable the "select all/none" section when selecting activities to rollover

  Scenario Outline: Display or hide the select all/none section
    Given all rollover protections are disabled                                                 # local_rollover
    And the course "Source" has an assignment "Final Exam"                                      # local_rollover
    And there is a course with shortname "Destination"                                          # local_rollover
    And I am an administrator                                                                   # local_rollover
    When I navigate to "Courses > Rollover settings > Select all/none" in site administration
    And I <Display> the checkbox "Display select all/none section"                              # local_rollover
    And I press "Save changes"
    And I go to the rollover page for the course "Destination"                                  # local_rollover
    And I select "Source" in "Source course"                                                  # local_rollover
    And I press "Next"
    And I select "Include activities and resources" in the list of activities/resources         # local_rollover
    And I press "Next"
    Then I should <Sees> the "Select all/none" section                                          # local_rollover

    Examples:
      | Display  | Sees    |
      | select   | see     |
      | deselect | not see |
