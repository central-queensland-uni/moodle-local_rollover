@local @local_rollover @javascript
Feature: Select source course for rollovers
  In order to perform a rollover
  As a course coordinator
  I want an easy way to select which activities and resources to rollover


  Scenario: No activities should be selected to rollover by default
    Given I am a teacher                                                                                # local_rollover
    And I can modify the following courses:                                                             # local_rollover
      | Source      |
      | Destination |
    And the course "Source" has an assignment "Final Exam"                                              # local_rollover
    And the course "Source" has a quiz "Quick Test"                                                     # local_rollover
    And the default rollover settings do not include anything by default                                # local_rollover
    And the following activity rollover rule exists:                                                    # local_rollover
      | rule        | activity | regex |
      | not default |          |       |
    When I go to the rollover page for the course "Destination"                                         # local_rollover
    And I select "Source" in "Original course"                                                          # local_rollover
    And I press "Next"
    And I select "Include activities and resources" in the list of activities/resources                 # local_rollover
    And I select "Include question bank" in the list of activities/resources                            # local_rollover
    And I press "Next"
    Then the activities options should be:                                                              # local_rollover
      | activity   | selected | modifiable |
      | Final Exam | no       | yes        |
      | Quick Test | no       | yes        |
