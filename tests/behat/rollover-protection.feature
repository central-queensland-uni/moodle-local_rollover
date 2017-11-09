@local @local_rollover @javascript
Feature: Rollover protection
  In order to prevent course coordinators to make mistakes when rolling over courses
  As a course coordinator
  I should receive errors or warnings as needed when rolling over courses


  Scenario: I don't see any warnings or errors
    Given I am a teacher                                                                          # local_rollover
    And the rollover protection is configured as follows:                                         # local_rollover
      | Protection                                  | Option |
      | If rollover destination is not empty        | ignore |
      | If rollover destination is not hidden       | ignore |
      | If rollover destination contains user data  | ignore |
      | If rollover destination has already started | ignore |
    And I can modify the the course "destination"                                                 # local_rollover
    And the "destination" course is not empty, is visible, has user data and has already started  # local_rollover
    When I go to the rollover page for the course "destination"                                   # local_rollover
    Then I should not see "Warning"
    And I should not see "Error"


  Scenario Outline: I see the warnings or errors
    Given I am a teacher                                                                          # local_rollover
    And the rollover protection is configured as follows:                                         # local_rollover
      | Protection                                  | Action   |
      | If rollover destination is not empty        | <Action> |
      | If rollover destination is not hidden       | <Action> |
      | If rollover destination contains user data  | <Action> |
      | If rollover destination has already started | <Action> |
    And I can modify the the course "destination"                                                 # local_rollover
    And the "destination" course is not empty, is visible, has user data and has already started  # local_rollover
    When I go to the rollover page for the course "destination"                                   # local_rollover
    Then I should see "<See>"
    And I should see "The destination course already contains activities."
#    And I should see "Destination course is visible"
#    And I should see "Destination course has user data"
#    And I should see "Destination course has already started"
    But I should not see "<Not see>"
    And I should <See Continue> the button "Continue"                                             # local_rollover

    Examples:
      | Action | See     | Not see | See Continue |
      | warn   | Warning | Error   | see          |
      | stop   | Error   | Warning | not see      |
