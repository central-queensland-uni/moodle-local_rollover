@local @local_rollover @javascript
Feature: Keep backup history
  In order to debug possible rollover problems
  As a site administrator
  I want to configure how to keep the backup files for further analysis

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Backup history" in site administration
    Then I should see "Backup history"
    And I should see "Path in server"
    And I should see "Retention period"

  Scenario: I can change the backup history settings
    Given I am an administrator                                             # local_rollover
    When I go to the "Rollover Backup history" settings page                # local_rollover
    And I set the field "Path in server" to "/tmp"
    And I set the duration field "Retention period" to 1 hour               # local_rollover
    And I press "Save"
    Then I should see "Changes saved"
