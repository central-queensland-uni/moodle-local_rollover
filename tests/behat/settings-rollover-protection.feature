@local @local_rollover @javascript
Feature: Adjust past instances filter
  In order to make it easy for course coordinators to perform rollovers
  As a site administrator
  I want to define a regular expression that defines how to identify past instances

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Rollover protection" in site administration
    Then I should see "Rollover protection"
    And I should see "If rollover destination is not empty"
    And I should see "If rollover destination is not hidden"
    And I should see "If rollover destination contains user data"
    And I should see "If rollover destination has already started"
