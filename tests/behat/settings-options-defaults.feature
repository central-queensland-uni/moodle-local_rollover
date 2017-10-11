@local @local_rollover @javascript
Feature: Adjust rollover settings
  In order to make it easy for course coordinators to perform rollovers
  As a site administrator
  I want to define defaults and settings for rollovers


  Scenario: I can navigate to the settings
    Given I am an administrator                                             # local_rollover
    When I navigate to "Courses > Rollover settings > Options defaults" in site administration
    Then I should see "Include users"
    And I should see "Anonymise information"
    And I should see "Include role assignments"
    And I should see "Include activities and resources"
    And I should see "Include blocks"
    And I should see "Include filters"
    And I should see "Include comments"
    And I should see "Include badges"
    And I should see "Include user completion information"
    And I should see "Include logs"
    And I should see "Include histories"
    And I should see "Include question bank"
    And I should see "Include groups and groupings"


  Scenario: I can set rollover option defaults and lock them
    Given I am an administrator                                                               # local_rollover
    And I am at the "Rollover options" settings page                                          # local_rollover
    When I set the following default options:                                                 # local_rollover
      | Option                           | Selected | Locked |
      | Include blocks                   |          |        |
      | Include question bank            | X        |        |
      | Include users                    |          | X      |
      | Include activities and resources | X        | X      |
    And I press "Save changes"
    And I am rolling over a course at the "Rollover options" step                             # local_rollover
    Then I should see the checkbox "Include blocks" unselected                                # local_rollover
    And I should see the checkbox "Include question bank" selected                            # local_rollover
    And I should not see "Include users"
    And I should see the checkbox "Include activities and resources" selected and disabled    # local_rollover
