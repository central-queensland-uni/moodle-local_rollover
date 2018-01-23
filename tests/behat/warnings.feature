@local @local_rollover @javascript
Feature: Show warnings in case of errors
  In order to be aware of possible errors during rollover
  As an administrator or course coordinator
  I want to view warning messages after a rollover with issues

  Scenario: Show a warning if there is a missing file
    Given I am an administrator                                             # local_rollover
    And the default rollover settings do not include anything by default    # local_rollover
    And all rollover protections are disabled                               # local_rollover
    And the course "source" has a page with the Catalyst logo               # local_rollover
    And the Catalyst logo is missing in the site data                       # local_rollover
    And there is a course with shortname "destination"                      # local_rollover
    And I am at the course "destination" page                               # local_rollover
    When I press "Rollover" in the Course Administration block              # local_rollover
    And I select "source" in "Source course"                             # local_rollover
    And I press "Next"
    And I select "Include activities and resources" in the list of activities/resources   # local_rollover
    And I press "Next"
    And I press "Next"
    And I press "Perform rollover"
    Then I should see "Rollover successful with warnings"
    And I should see "files could not be saved"
