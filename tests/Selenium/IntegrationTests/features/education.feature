Feature: Education
  As a user I want to view courses and articles

  Background:
    Given Taklimakan Network /courses page is opened and start popup is skipped
    Then I should see 'Taklimakan' page

  @sanity
  Scenario: Open course from index page
    When I open Blockchain Course by Complexity Labs course from courses index page
    Then I should see Blockchain Course by Complexity Labs course view page

  Scenario: Start course from view page

  Scenario: Share course

  Scenario: Comment course via Disqus

  Scenario: Navigate through course materials

  Scenario: Open material from course page

  Scenario: Check write to us link

  Scenario: Share material

  Scenario: Comment material via Disqus

  Scenario: Navigate to next material