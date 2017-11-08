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
