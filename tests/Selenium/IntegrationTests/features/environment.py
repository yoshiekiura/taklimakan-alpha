"""
 environment.py

 PURPOSE: This file should contain all hooks for Gherkin tests:
   before_all - this hook will be called before all bunch of tests execute
     before_feature - this hook will be called before each feature
       before_scenario - this hook will be called before each scenario
         before_step - this hook will be called before each step (When, Given etc.)
         after_step - this hook will be called after each step (When, Given etc.) executed
       after_scenario - this hook will be called after each scenario executed
     after_feature - this hook will be called after each feature executed
     before_tag, after_tag - These run before and after a section tagged with the given name.
       They are invoked for each tag encountered in the order they’re found in the feature file.

 ATTENTION:
   This file MUST be placed on the same level as *.feature files in folder structure
     otherwise these hooks will not be executed
"""
import os
from selenium import webdriver
from selenium.webdriver.chrome.options import Options


def before_all(context):
    """
    This hook is called before all features and called only once during execution.
    This script open new selenium web-driver (Headless browser for Jenkins and Chrome otherwise)
    :param context: test context
    :return: none
    """
    # print("before all scenario hook\n")
    # Verify that this is not Jenkins server
    if os.environ.get('BRANCH_NAME') is None:
        # this is not Jenkins open regular Chrome browser
        context.browser = webdriver.Chrome()
        context.browser.maximize_window()
    else:
        # this is Jenkins. Open headless browser
        chrome_options = Options()
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--window-size=1920x1080")
        context.browser = webdriver.Chrome(chrome_options=chrome_options)


def after_all(context):
    """
    Close Selenium web-driver at the end of all tests execution
    :param context: test context
    :return: none
    """
    # print("after all scenario hook\n")
    context.browser.quit()


def before_scenario(context, scenario):
    """
    This hook executed before scenario and try to reach Taklimakan main page
    :param context: test context
    :param scenario: current scenario name (not used for now)
    :return: none
    """
    if os.environ.get('DEPLOY_HOST') is None:
        os.environ["DEPLOY_HOST"] = 'tkln-test.usetech.ru'

    context.browser.get('http://'+os.environ.get('DEPLOY_HOST'))