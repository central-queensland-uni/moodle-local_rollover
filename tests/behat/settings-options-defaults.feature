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
