Feature: Smoke test

  @smoke
  Scenario: Check Taklimakan main page is loaded without errors
    Given Taklimakan Network Main page is opened and start popup is skipped
    Then I should see 'Taklimakan' page
    And no exceptions on a page