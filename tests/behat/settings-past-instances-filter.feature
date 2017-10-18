@local @local_rollover @javascript
Feature: Adjust past instances filter
  In order to make it easy for course coordinators to perform rollovers
  As a site administrator
  I want to define a regular expression that defines how to identify past instances

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Past instances filter" in site administration
    Then I should see "Past instances filter"
    And I should see "Regular Expression"

  Scenario: I can change the regex.
    Given I am an administrator                                       # local_rollover
    When I go to the "Rollover past instances filter" settings page   # local_rollover
    And I set the field "Regular Expression" to "^RegEx$"
    And I press "Save"
    Then I should see "saved"
