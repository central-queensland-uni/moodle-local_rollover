@local @local_rollover @javascript
Feature: Enable rollover protection
  In order to prevent course coordinators to make mistakes when rolling over courses
  As a site administrator
  I want to enable warnings or errors if some conditions fail before the rollover

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Rollover protection" in site administration
    Then I should see "Rollover protection"
    And I should see "If rollover destination is not empty"
    And I should see "If rollover destination is not hidden"
    And I should see "If rollover destination contains students"
    And I should see "If rollover destination has already started"

  Scenario: I can change the rollover protection settings
    Given I am an administrator                                             # local_rollover
    When I go to the "Rollover protection" settings page                    # local_rollover
    And I set the field "If rollover destination is not empty" to "do not show warning"
    And I set the field "If rollover destination is not hidden" to "show warning"
    And I set the field "If rollover destination contains students" to "stop and prevent rollover"
    And I set the field "If rollover destination has already started" to "stop and prevent rollover"
    And I press "Save"
    Then I should see "Changes saved"
