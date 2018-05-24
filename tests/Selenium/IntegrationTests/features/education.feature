Feature: Education
  As a user I want to view courses and articles

  Background:
    Given Taklimakan Network Main page is opened and start popup is skipped
    Then I should see 'Taklimakan' page
    Given The course Blockchain Course by Complexity Labs exists
    When I click 'Education' button in top menu

  @sanity
  Scenario: Open course from index page
    When I open Blockchain Course by Complexity Labs course from courses index page
    Then I should see Blockchain Course by Complexity Labs course view page

  @sanity
  Scenario: Start course from view page
    When I open Blockchain Course by Complexity Labs course from courses index page
    And I click on Start Course button
    Then I should see Introduction page of Blockchain Course by Complexity Labs course

  @sanity
  Scenario: Share course in twitter
    When I open Blockchain Course by Complexity Labs course from courses index page
    And I click Share in Twitter button
    Then I should see social network Twitter popup
    When I login in Twitter with test account
  #TODO continue when twitter registration is fixed

  @sanity
  Scenario: Comment course via Disqus
    When I open Blockchain Course by Complexity Labs course from courses index page
    And I login with Disqus test account
    And I post a Disqus comment 'Hello there'
    Then I should see my Disqus comment 'Hello there' published

  #Scenario: Navigate through course materials

  Scenario: Open material from course page

  Scenario: Check write to us link

  Scenario: Share material

  Scenario: Comment material via Disqus

  Scenario: Navigate to next material