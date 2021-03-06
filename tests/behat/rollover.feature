@local @local_rollover @javascript
Feature: Configure and perform course rollovers.
  In order to test and understand the main features of this plugin
  As a stakeholder or developer
  I want to run the happy path for the main use cases

  These are tests written for stakeholders to test and demonstrate how to use the plugin.


  Scenario Outline: Perform a course rollover with activities
    Given I am an administrator                                                          # local_rollover
    And the default rollover settings do not include anything by default                 # local_rollover
    And all rollover protections are disabled                                            # local_rollover
    And there is a course with shortname "ABC123-2017-1"                                 # local_rollover
    And the course "ABC123-2017-1" has an assignment "Final Exam"                        # local_rollover
    And the course "ABC123-2017-1" has an HTML block "Why study ABC?"                    # local_rollover
    And there is a course with shortname "ABC123-2017-2"                                 # local_rollover
    And I am at the course "ABC123-2017-2" page                                          # local_rollover

    When I press "Rollover" in the Course Administration block                           # local_rollover
    Then I should see "Rollover: Select source course"

    When I select "ABC123-2017-1" in "Source course"                                   # local_rollover
    And I press "Next"
    Then I should see "Rollover: Select content options"

    When I select the rollover options "<selected options>"                              # local_rollover
    And I press "Next"
    Then I should see "Rollover: Select activities and resources"

    When I select "<selected activities>" in the list of activities/resources            # local_rollover
    And I deselect "<unselected activities>" in the list of activities/resources         # local_rollover
    And I press "Next"
    Then I should see "Rollover: Confirm settings"

    When I press "Perform rollover"
    Then I should see "Rollover successful"
    And I should see "ABC123-2017-1 rolled over into ABC123-2017-2"

    When I press "Proceed to course"
    Then I should see "ABC123-2017-2"
    And I should see the following "<rollover>"                                          # local_rollover
    But I should not see the following "<no rollover>"                                   # local_rollover

    Examples:
      | selected options                 | selected activities | unselected activities | rollover      | no rollover               |
      | Include activities and resources | General; Final Exam |                       | Final Exam    |                           |
      | Include activities and resources | General             | Final Exam            |               | Final Exam                |
      | Include blocks                   |                     |                       | Why study ABC |                           |
      |                                  |                     |                       |               | Final Exam; Why study ABC |
