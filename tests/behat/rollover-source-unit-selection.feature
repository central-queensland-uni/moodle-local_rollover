@local @local_rollover @javascript
Feature: Select source course for rollovers
  In order to perform a rollover
  As a course coordinator
  I want an easy way to select which course to rollover from


  Scenario: Display a list with all my courses
    Given I am a teacher                                            # local_rollover
    And I can modify the following courses:                         # local_rollover
      | Language-2017-2   |
      | English-2017-1    |
      | English-2016-2    |
      | Portuguese-2017-1 |
    But I cannot modify the following courses:                      # local_rollover
      | JavaScript-2017-1 |
    When I go to the rollover page for the course "Language-2017-2" # local_rollover
    Then I should see the following source options:                 # local_rollover
      | English-2017-1    |
      | English-2016-2    |
      | Portuguese-2017-1 |
    And I should not see the following source options:              # local_rollover
      | Language-2017-2   | because this is the course rolling over into |
      | JavaScript-2017-1 | because I cannot modify this course          |
