@local @local_rollover @javascript
Feature: Select source course for rollovers
  In order to perform a rollover
  As a course coordinator
  I want an easy way to select which course to rollover from

  Background:
    Given all rollover protections are disabled     # local_rollover


  Scenario: Display a list courses that I can modify
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


  Scenario: Display past instances courses even if I cannot modify it
    Given I am a teacher                                            # local_rollover
    And I can modify the following courses:                         # local_rollover
      | Esperanto-2017 |
      | Spanish-2017   |
    But I cannot modify the following courses:                      # local_rollover
      | Esperanto-1999 |
      | Spanish-1999   |
    And the past instances RegEx is set to "/^([^-]+)-\d{4}$/"      # local_rollover
    When I go to the rollover page for the course "Spanish-2017"    # local_rollover
    Then I should see the following source options:                 # local_rollover
      | Esperanto-2017 | because I can modify it       |
      | Spanish-1999   | because it is a past instance |
    And I should not see the following source options:              # local_rollover
      | Spanish-2017   | because this is the course rolling over into             |
      | Esperanto-1999 | because I cannot modify it and it is not a past instance |
