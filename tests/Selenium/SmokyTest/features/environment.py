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
import re

from selenium import webdriver
from selenium.webdriver.chrome.options import Options


def scenario_name(context):
    """
    Get scenario name from context.scenario and replace spaces with underscores
    :param context: behave.runner.Context
    :return: scenario name

    WARNING: this function should be called only in scenario scope, for example in before_all it failed
    """
    return re.sub(r' ', '_', context.scenario.name)



def before_all(context):
    """
    This hook is called before all features and called only once during execution.
    This script open new selenium web-driver (Headless browser for Jenkins and Chrome otherwise)
    :param context: behave.runner.Context
    :return: none
    """
    # print("before all scenario hook\n")

    # Verify that this is not Jenkins server
    if os.environ.get('BRANCH_NAME') is None:
        # this is not Jenkins open regular Chrome browser
        context.browser = webdriver.Chrome()
    else:
        # this is Jenkins. Open headless browser
        chrome_options = Options()
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--window-size=1920x1080")
        context.browser = webdriver.Chrome(chrome_options=chrome_options)

    if os.environ.get('DEPLOY_HOST') is None:
        os.environ["DEPLOY_HOST"] = 'tkln-test.usetech.ru'
    print('Test executed on: ' + os.environ["DEPLOY_HOST"]+'\n')


def after_all(context):
    """
    Close Selenium web-driver at the end of all tests execution
    :param context: behave.runner.Context
    :return: none
    """
    # print("after all scenario hook\n")
    context.browser.quit()


def before_scenario(context, scenario):
    """
    This hook executed before scenario and try to reach Taklimakan main page
    :param context: behave.runner.Context
    :param scenario: current scenario name (not used for now)
    :return: none
    """

    # remove previously created screenshots if exist
    if os.path.exists('Screenshots/' + scenario_name(context) + '.png'):
        os.remove('Screenshots/' + scenario_name(context) + '.png')

    context.browser.get('http://'+os.environ.get('DEPLOY_HOST'))
