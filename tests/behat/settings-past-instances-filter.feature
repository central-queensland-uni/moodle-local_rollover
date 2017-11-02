@local @local_rollover @javascript
Feature: Adjust past instances filter
  In order to make it easy for course coordinators to perform rollovers
  As a site administrator
  I want to define a regular expression that defines how to identify past instances

  Scenario: I can navigate to it.
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Past instances filter" in site administration
    Then I should see "Past instances filter"
    And I should see "Regular Expression"
    But I should not see "saved"

  Scenario: I can change the regex.
    Given I am an administrator                                       # local_rollover
    When I go to the "Rollover past instances filter" settings page   # local_rollover
    And I set the field "Regular Expression" to "/^Reg(Ex)$/"
    And I press "Save"
    Then I should see "saved"

  Scenario: I can see samples
    Given I am an administrator                                       # local_rollover
    And there is a course with shortname "ABC-123"                    # local_rollover
    And there is a course with shortname "ABC-456"                    # local_rollover
    And there is a course with shortname "ABC-NEW"                    # local_rollover
    And there is a course with shortname "DEF-789"                    # local_rollover
    When I go to the "Rollover past instances filter" settings page   # local_rollover
    And I set the field "Regular Expression" to "/^(.*)-\d{3}$/"
    Then I should see "ABC-123"
    And I should see "ABC-456"
    And I should see "DEF-789"
    But I should not see "ABC-NEW"

    Scenario: I see an error message if the RegEx is not valid
      Given I am an administrator                                       # local_rollover
      When I go to the "Rollover past instances filter" settings page   # local_rollover
      And I set the field "Regular Expression" to "/^(.*)"
      Then I should see "Error"
      And I should see "malformed"
