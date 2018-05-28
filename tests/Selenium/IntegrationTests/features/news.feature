Feature: News
  As a user I want to read News

  Background:
    Given Taklimakan Network Main page is opened and start popup is skipped
    Then I should see 'Taklimakan' page
    When I click 'News' button in top menu

  @sanity
  Scenario: Open a news from news index page
    When I open first news link on news index page
    Then I should see first news view page

  @sanity
  Scenario: Filter news by tag
    When I select BTC tag from tags list
    Then I should see BTC tag in the URL

  @sanity
  Scenario: Load more
    When I click Load more button
    Then I should see more content loaded

  Scenario: Open news source from index page

  Scenario: Open news source from view page

  Scenario: Check tags on view page

  #Scenario: Comment news using Disqus - cannot be automated because of disqus captcha

  Scenario: Share news from view page