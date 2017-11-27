@local @local_rollover @javascript
Feature: Keep backup history
  In order to debug possible rollover problems
  As a site administrator
  I want to configure how to keep the backup files for further analysis

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Backup history" in site administration
    Then I should see "Backup history"
    And I should see "Retention period"
