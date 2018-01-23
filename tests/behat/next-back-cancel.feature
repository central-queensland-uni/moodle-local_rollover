@local @local_rollover @javascript
Feature: Allow navigating back When adjusting a rollober
  In order to easily change options during a rollover
  As an administrator or course coordinator
  I want to be able to go back after clicking next or cancel at anytime

  Scenario: Click next, back and cancel
    Given I am an administrator                                                     # local_rollover
    And the default rollover settings do not include anything by default            # local_rollover
    And all rollover protections are disabled                                       # local_rollover
    And there is a course with shortname "ABC123-2017-1"                            # local_rollover
    And the course "ABC123-2017-1" has an assignment "Final Exam"                   # local_rollover
    And the course "ABC123-2017-1" has an HTML block "Why study ABC?"               # local_rollover
    And there is a course with shortname "ABC123-2017-2"                            # local_rollover
    And I am at the course "ABC123-2017-2" page                                     # local_rollover

    When I press "Rollover" in the Course Administration block                      # local_rollover
    Then I should see "Select source course"
    And I should see the button "Cancel"                                            # local_rollover
    But I should not see the button "Back"                                          # local_rollover

    When I select "ABC123-2017-1" in "Original course"                              # local_rollover
    And I press "Next"
    Then I should see the button "Back"                                             # local_rollover

    When I select "Include activities and resources" in the list of activities/resources    # local_rollover
    And I press "Next"
    And I press "Back"
    Then I should see the checkbox "Include activities and resources" selected      # local_rollover

    When I press "Next"
    And I press "Next"
    And I press "Cancel"
    Then I should be at the course "ABC123-2017-2" page                             # local_rollover

  Scenario: Restart rollover if another source course selected
    Given I am an administrator                                                     # local_rollover
    And the default rollover settings do not include anything by default            # local_rollover
    And all rollover protections are disabled                                       # local_rollover
    And the course "Test-Source-A" has an assignment "Assignment Alpha"             # local_rollover
    And the course "Test-Source-B" has an assignment "Assignment Bravo"             # local_rollover
    And there is a course with shortname "Test-Destination"                         # local_rollover
    And I am at the course "Test-Destination" page                                  # local_rollover

    When I press "Rollover" in the Course Administration block                      # local_rollover
    And I select "Test-Source-A" in "Original course"                               # local_rollover
    And I press "Next"
    And I press "Next" again                                                        # local_rollover
    Then I should see "Assignment Alpha"
    But I should not see "Assignment Bravo"

    When I press "Back"
    And I press "Back" again                                                        # local_rollover
    And I select "Test-Source-B" in "Original course"                               # local_rollover
    And I press "Next"
    And I press "Next" again                                                        # local_rollover
    Then I should see "Assignment Bravo"
    But I should not see "Assignment Alpha"
