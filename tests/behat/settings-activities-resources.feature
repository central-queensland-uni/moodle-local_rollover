@local @local_rollover @javascript
Feature: Rules for rolling over activities and resources
  In order to prevent duplication or missing activities in rollover
  As a site administrator
  I want to define a way to enforce activities to be rolled over or blocked from rolling over

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Activities & Resources" in site administration
    Then I should see "Activities and resources rollover rules"
    And I should see "No rules"

  Scenario: I can view current rules.
    Given I am an administrator                                             # local_rollover
    And the following activity rollover rules exist:                        # local_rollover
      | rule        | activity   | regex             |
      | enforce     | Assignment |                   |
      | forbid      | Forum      | /^Announcements$/ |
      | not default |            | /^.*TEST.*$/      |
    When I go to the "Activities & Resources" settings page                 # local_rollover
    Then I should not see "No rules"
    And I should see "Forbid rolling over any 'Forum' matching:"
    And I should see "Enforce rolling over all 'Assignment' activities."
    And I should see "Do not rollover by default any activity matching:"

  Scenario: I can add a new rule.
    Given I am an administrator                                            # local_rollover
    And I am at the "Activities & Resources" settings page                 # local_rollover
    When I press "Add new rule"
    And I set the field "Rule" to "Enforce"
    And I set the field "Activity" to "Quiz"
    And I set the field "Regular Expression" to "/^Final Exam$/"
    And I press "Add rule"
    Then I should see "Rule #1"

  Scenario: I can edit an existing rule.
    Given I am an administrator                                            # local_rollover
    And the following activity rollover rules exist:                       # local_rollover
      | rule    | activity   | regex |
      | enforce | Assignment |       |
    And I am at the "Activities & Resources" settings page                 # local_rollover
    When I follow "Change rule"
    And I set the field "Activity" to "Wiki"
    And I set the field "Regular Expression" to "/^All time wiki$/"
    And I press "Update rule"
    Then I should see "Rule #1"
    And I should see "Wiki"
    And I should see "/^All time wiki$/"
    But I should not see "Rule #2"

  Scenario: I can cancel when adding a new rule.
    Given I am an administrator                                            # local_rollover
    And I am at the "Activities & Resources" settings page                 # local_rollover
    When I press "Add new rule"
    And I press "Cancel"
    Then I should see "No rules"

  Scenario: I can remove an existing rule.
    Given I am an administrator                                            # local_rollover
    And the following activity rollover rules exist:                       # local_rollover
      | rule    | activity   | regex |
      | enforce | Assignment |       |
    And I am at the "Activities & Resources" settings page                 # local_rollover
    When I follow "Remove rule"
    And I press "Remove rule"
    Then I should see "No rules"

  Scenario: I can cancel when adding a new rule.
    Given I am an administrator                                            # local_rollover
    And the following activity rollover rules exist:                       # local_rollover
      | rule    | activity   | regex             |
      | enforce | Assignment | /^My Funny Rule$/ |
    And I am at the "Activities & Resources" settings page                 # local_rollover
    When I follow "Remove rule"
    And I press "Cancel"
    Then I should see "/^My Funny Rule$/"

  Scenario: I can see samples of activities where the name ends with "Research"
    Given I am an administrator                                       # local_rollover
    And there is a course with shortname "My Course"                  # local_rollover
    And the course "My Course" has an assignment "First Research"     # local_rollover
    And the course "My Course" has an assignment "Second Research"    # local_rollover
    And the course "My Course" has an assignment "Research Ideas"     # local_rollover
    When I go to the "Activities & Resources" settings page           # local_rollover
    And I press "Add new rule"
    And I set the field "Regular Expression" to "/^.*Research$/"
    Then I should see "First Research"
    And I should see "Second Research"
    But I should not see "Research Ideas"
