@local @local_rollover @javascript
Feature: Select source course for rollovers
  In order to perform a rollover
  As a course coordinator
  I want an easy way to select which activities and resources to rollover

  Background:
    Given all rollover protections are disabled     # local_rollover

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

  Scenario: No assignments should be selected to rollover by default
    Given I am a teacher                                                                                # local_rollover
    And I can modify the following courses:                                                             # local_rollover
      | Source      |
      | Destination |
    And the course "Source" has an assignment "Final Exam"                                              # local_rollover
    And the course "Source" has a quiz "Quick Test"                                                     # local_rollover
    And the default rollover settings do not include anything by default                                # local_rollover
    And the following activity rollover rule exists:                                                    # local_rollover
      | rule        | activity   | regex |
      | not default | assignment |       |
    When I go to the rollover page for the course "Destination"                                         # local_rollover
    And I select "Source" in "Original course"                                                          # local_rollover
    And I press "Next"
    And I select "Include activities and resources" in the list of activities/resources                 # local_rollover
    And I select "Include question bank" in the list of activities/resources                            # local_rollover
    And I press "Next"
    Then the activities options should be:                                                              # local_rollover
      | activity   | selected | modifiable |
      | Final Exam | no       | yes        |
      | Quick Test | yes      | yes        |

  Scenario: Any "important" activity must be included in the rollover
    Given I am a teacher                                                                                # local_rollover
    And I can modify the following courses:                                                             # local_rollover
      | Source      |
      | Destination |
    And the course "Source" has an assignment "Exam 1 - very important"                                 # local_rollover
    And the course "Source" has an assignment "Final Exam"                                              # local_rollover
    And the course "Source" has a quiz "An important quiz"                                              # local_rollover
    And the default rollover settings do not include anything by default                                # local_rollover
    And the following activity rollover rule exists:                                                    # local_rollover
      | rule    | activity | regex             |
      | enforce |          | /^.*important.*$/ |
    When I go to the rollover page for the course "Destination"                                         # local_rollover
    And I select "Source" in "Original course"                                                          # local_rollover
    And I press "Next"
    And I select "Include activities and resources" in the list of activities/resources                 # local_rollover
    And I select "Include question bank" in the list of activities/resources                            # local_rollover
    And I press "Next"
    Then the activities options should be:                                                              # local_rollover
      | activity                | selected | modifiable |
      | Exam 1 - very important | yes      | no         |
      | Final Exam              | yes      | yes        |
      | An important quiz       | yes      | no         |

  Scenario: The announcements forum should not be allowed to rollover
    Given I am a teacher                                                                                # local_rollover
    And I can modify the following courses:                                                             # local_rollover
      | Source      |
      | Destination |
    And the course "Source" has a forum "Announcements"                                                 # local_rollover
    And the course "Source" has a forum "FAQ"                                                           # local_rollover
    And the course "Source" has an assignment "Final Assignment"                                        # local_rollover
    And the default rollover settings do not include anything by default                                # local_rollover
    And the following activity rollover rule exists:                                                    # local_rollover
      | rule   | activity | regex             |
      | forbid | forum    | /^Announcements$/ |
    When I go to the rollover page for the course "Destination"                                         # local_rollover
    And I select "Source" in "Original course"                                                          # local_rollover
    And I press "Next"
    And I select "Include activities and resources" in the list of activities/resources                 # local_rollover
    And I press "Next"
    Then the activities options should be:                                                              # local_rollover
      | activity         | selected | modifiable |
      | Announcements    | no       | no         |
      | FAQ              | yes      | yes        |
      | Final Assignment | yes      | yes        |
