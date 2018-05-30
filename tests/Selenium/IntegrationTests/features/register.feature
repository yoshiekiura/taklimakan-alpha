Feature: Register on Taklimakan Network
  As a user I want to be able to register on Taklimakan Network

  Background:
    Given Taklimakan Network Main page is opened and start popup is skipped
    Then I should see 'Taklimakan' page

  @sanity
  Scenario: Successful registration without wallet
    When I click 'Sign Up' button in top menu
    And I fill in registration form 'without' wallet
    And I submit the form
    Then I should see Email Verification form
  #Cannot proceed now as no emails are sent from dev and test servers

  @sanity
  Scenario: Successful registration with a wallet
    When I click 'Sign Up' button in top menu
    And I fill in registration form with wallet
    And I submit the form
    Then I should see Email Verification form
  #Cannot proceed now as no emails are sent from dev and test servers

  Scenario: Empty fields validation
    When I click 'Sign Up' button in top menu
    And I submit the form
    Then I should see registration form validation messages


  Scenario: Email field validation

  Scenario: Wallet field validation

  Scenario: Password field validation

  Scenario: Confirm password field validation

  Scenario: Enter invalid registration code

  Scenario: Register two user on the same wallet