@local @local_rollover @javascript
Feature: Rules for rolling over activities and resources
  In order to prevent duplication or missing activities in rollover
  As a site administrator
  I want to define a way to enforce activities to be rolled over or blocked from rolling over

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Activities & Resources" in site administration
    Then I should see "Activities & Resources Rollover Rules"
    And I should see "No rules"

  Scenario: I can view current rules.
    Given I am an administrator                                             # local_rollover
    And the following activity rollover rules exist:                        # local_rollover
      | rule        | module     | regex             |
      | enforce     | Assignment |                   |
      | forbid      | Forum      | /^Announcements$/ |
      | not default |            | /^.*TEST.*$/      |
    When I go to the "Activities & Resources" settings page                 # local_rollover
    Then I should not see "No rules"
    But I should see "Rule #1: Forbid rolling over any 'Forum' matching: /^Announcements$/"
    And I should see "Rule #2: Enforce rolling over all 'Assignment' activities."
    And I should see "Rule #3: Do not rollover by default any activity matching: /^.*TEST.*$/"
