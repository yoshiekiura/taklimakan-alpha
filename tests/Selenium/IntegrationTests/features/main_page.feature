Feature: Main page
  As a user I want to read and access various sections from the main page

  Background:
    Given Taklimakan Network Main page is opened and start popup is skipped
    Then I should see 'Taklimakan' page

  Scenario: Open news list from the main page by See all button
    When I click 'See all news' on the main page
    Then I should see 'News' page

  Scenario: Open news list from the main page by top menu button
    When I click 'News' button in top menu
    Then I should see 'Taklimakan News' page

  Scenario: Open courses list from the main page by See all button
    When I click 'See all Courses and Articles' on the main page
    Then I should see 'Taklimakan Courses' page

  Scenario: Open courses list from the main page by top menu button
    When I click 'Education' button in top menu
    Then I should see 'Taklimakan Courses' page

  Scenario: Open Analytics from banner
    When I click 'View Analytics' on the main page
    Then I should see Crypto100 chart

  Scenario: Open Analytics from top menu
    When I click 'Analytics' button in top menu
    Then I should see Crypto100 chart

  Scenario: Successful subsription to news
    When I input test@yopmail.com into subscription form
    And I click on Subscribe button
    Then I should see You have been subscribed message

  Scenario Outline: Email field validation
    When I input <incorrect_email> into subscription form
    And I click on Subscribe button
    Then I should see validation message
    Examples:
      | incorrect_email |
      | mytest@         |
      | mytest@mail     |
      | not_an_email    |
      |                 |