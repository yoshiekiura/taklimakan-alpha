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
  Scenario: Open material from course page
    When I open Blockchain Course by Complexity Labs course from courses index page
    And I click on Smart Contracts material
    Then I should see Smart Contracts page of Blockchain Course by Complexity Labs course

  @sanity
  Scenario: Check write to us link
    When I open Blockchain Course by Complexity Labs course from courses index page
    Then I check Write to us link

#  Scenario: Share course in twitter
#    When I open Blockchain Course by Complexity Labs course from courses index page
#    And I click Share in Twitter button
#    Then I should see social network Twitter popup
#    When I login in Twitter with test account
  #TODO continue when twitter registration is fixed

  #Scenario: Comment course via Disqus - cannot be automated now because of captcha in Disqus
  #Scenario: Comment material via Disqus - same

  #Scenario: Navigate through course materials

  #Scenario: Share material continue when twitter registration works

  #Scenario: Navigate to next material - not implemented on tkln yet