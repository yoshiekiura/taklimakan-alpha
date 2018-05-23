Feature: Analytics
  As a user I want to view analytical charts
  Background:
    Given Taklimakan Network /charts/all page is opened and start popup is skipped
    Then I should see 'Taklimakan' page

  @sanity
  Scenario: Check navigation to charts
    When I click on Crypto100 button
    Then I should see crypto-index chart in the URL
    When I click on Price button
    Then I should see price chart in the URL
    When I click on Volatility button
    Then I should see volatility chart in the URL
    When I click on Alpha button
    Then I should see alpha chart in the URL
    When I click on Beta button
    Then I should see beta chart in the URL
    When I click on Sharpe ratio button
    Then I should see sharpe chart in the URL

  #Scenario: Zoom charts

  @sanity
  Scenario: Change currency pairs
    When I select ETH-USD pair for charts
    Then I should see ETH-USD pair on analytics page
    
  #Scenario: Read the legend for each chart