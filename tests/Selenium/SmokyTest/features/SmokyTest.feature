Feature: Smoky test to verify that deploy is success
  The Smoky test must be very short and quit. It should not verify all or main functionality.
  It should verify that web-page available (no 404 or 500 etc). And no exception exist on a page which means that
    Symfony start successfully and no database issues etc.
  Smoky test is used by Jenkins to understand that build deployed successfully.
    It means that build could be used for testing purposes if it is develop or release or it is in production if master

  Scenario: Smoky test
  This scenario open web-browser and go to Taklimakan web-page and verify that page is reached and no exceptions on it

    When Taklimakan Network web-page is opened

    Then no exceptions on a page