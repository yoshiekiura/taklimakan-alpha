Feature: Main page
  As a user I want to read and access various sections from the main page

  Background:
    Given Taklimakan Network is opened and start popup is skipped

  Scenario: Open news list from the main page by See all button
    When I click See all news on the main page
    Then I should see News index page

 # Scenario: Open news list from the main page by top menu button
 #   When I click News button in top menu
 #   Then I should see News index page

  #Scenario: Open courses list from the main page by See all button
  #  When I click See all Courses and Articles on the main page
  #  Then I should see Courses index page

  #Scenario: Open courses list from the main page by top menu button
  #  When I click Education button in top menu
  #  Then I should see Courses index page

  #Scenario: Open Analytics from banner

  #Scenario: Open Analytics from top menu

  #Scenario: Subscribe to news

  #Scenario: Check footer links