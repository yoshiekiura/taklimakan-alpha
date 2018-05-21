Feature: Analytics
  As a user I want to view analytical charts
  Background:
    Given Taklimakan Network /charts/all page is opened and start popup is skipped
    Then I should see 'Taklimakan' page

  @sanity
  Scenario: Check all charts are visible: TN Crypto 100/Price/ Volatility/Alpha/Beta/Sharpe Ratio
    When I click on Crypto100 button
    Then I should see active Crypto100 chart

  Scenario: Zoom charts

  Scenario: Change currency pairs

  Scenario: Read the legend for each chart