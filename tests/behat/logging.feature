@local @local_rollover @javascript
Feature: Log rollover attempts
  In order to monitor and check rollover attempts
  As an administrator
  I want to see when users accessed, started and completed rollovers.

  Scenario: Perform a course while checking the log
    Given I am an administrator                                                                   # local_rollover
    And I performed a rollover from course "ABC123-2017-1" into "ABC123-2017-2"                   # local_rollover
    When I view the logs page                                                                     # local_rollover
    Then I should see "Rollover requested"
    And I should see "requested to rollover course id"
    And I should see "Rollover started"
    And I should see "started to rollover course id"
    And I should see "Rollover completed"
    And I should see "completed rollover from course id"
    And I should see "with backup file:"
