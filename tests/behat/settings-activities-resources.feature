@local @local_rollover @javascript
Feature: Rules for rolling over activities and resources
  In order to prevent duplication or missing activities in rollover
  As a site administrator
  I want to define a way to enforce activities to be rolled over or blocked from rolling over

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Activities & Resources" in site administration
    Then I should see "Activities & Resources Rollover Rules"
