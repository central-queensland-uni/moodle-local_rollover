@local @local_rollover @javascript
Feature: Adjust past instances filter
  In order to make it easy for course coordinators to perform rollovers
  As a site administrator
  I want to define a regular expression that defines how to identify past instances


  Scenario: I can navigate to the settings
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Past instances filter" in site administration
    Then I should see "Past instances filter"
    And I should see "Regular Expression"
