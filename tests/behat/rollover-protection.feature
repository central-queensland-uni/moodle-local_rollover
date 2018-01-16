@local @local_rollover @javascript
Feature: Rollover protection
  In order to prevent course coordinators to make mistakes when rolling over courses
  As a course coordinator
  I should receive errors or warnings as needed when rolling over courses


  Scenario: I don't see any warnings or errors
    Given I am a teacher                                                                          # local_rollover
    And the rollover protection is configured as follows:                                         # local_rollover
      | Protection                                  | Action |
      | If rollover destination is not empty        | ignore |
      | If rollover destination is not hidden       | ignore |
      | If rollover destination contains students   | ignore |
      | If rollover destination has already started | ignore |
    And I can modify the the course "destination"                                                 # local_rollover
    And the "destination" course is not empty, is visible, has a student and has already started  # local_rollover
    When I go to the rollover page for the course "destination"                                   # local_rollover
    Then I should not see "Warning"
    And I should not see "Error"


  Scenario Outline: I see the warnings or errors
    Given I am a teacher                                                                          # local_rollover
    And the rollover protection is configured as follows:                                         # local_rollover
      | Protection                                  | Action   |
      | If rollover destination is not empty        | <Action> |
      | If rollover destination is not hidden       | <Action> |
      | If rollover destination contains students   | <Action> |
      | If rollover destination has already started | <Action> |
    And I can modify the the course "destination"                                                 # local_rollover
    And the "destination" course is not empty, is visible, has a student and has already started  # local_rollover
    When I go to the rollover page for the course "destination"                                   # local_rollover
    Then I should see "<See>"
    And I should see "The destination course already contains activities."
    And I should see "The destination course is already visible."
    And I should see "The destination course has enrolled students."
    And I should see "The destination course has already started."
    But I should not see "<Not see>"
    And I should <See Continue> the button "Continue"                                             # local_rollover

    Examples:
      | Action | See     | Not see | See Continue |
      | warn   | Warning | Error   | see          |
      | stop   | Error   | Warning | not see      |


  Scenario: I see the custom message for warnings or errors
    Given I am a teacher                                                                          # local_rollover
    And the rollover protection is configured as follows:                                         # local_rollover
      | Protection                                  | Action | Text                      |
      | If rollover destination is not empty        | warn   | The destination has stuff |
      | If rollover destination is not hidden       | warn   | I can see it              |
      | If rollover destination contains students   | stop   | There are students here   |
      | If rollover destination has already started | stop   | You are late              |
    And I can modify the the course "destination"                                                 # local_rollover
    And the "destination" course is not empty, is visible, has a student and has already started  # local_rollover
    When I go to the rollover page for the course "destination"                                   # local_rollover
    Then I should see "Warning"
    And I should see "Error"
    And I should see "The destination has stuff"
    And I should see "I can see it"
    And I should see "There are students here"
    And I should see "You are late"
