@local @local_rollover @javascript
Feature: Test behat
  In order to test the plugin
  As a developer
  I need behat tests

  Scenario: Test context file
    Given I log in as "admin"
    When I do nothing
    Then I should see "Behat"
