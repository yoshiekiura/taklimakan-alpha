import re
import os
import requests

import time

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.common.by import By

"""
NB!!! All assertions should be done only in @then steps
Wherenever possible provide readable error messages for assertions
"""
# TODO print current url if any step fails (should be in env.py)
"""
###GIVEN###
"""


def scenario_name(context):
    """
    Get scenario name from context.scenario and replace spaces with underscores
    :param context: behave.runner.Context
    :return: scenario name

    WARNING: this function should be called only in scenario scope, for example in before_all it failed
    """
    return re.sub(r' ', '_', context.scenario.name)


def verify(context, state, fail_text):
    """
    If state is fail create screenshot and store it (it will be stored as build artifact by Jenkins)
    WARNING: use this function if it is necessary to store screenshot!
      Otherwise use assert since it will be more clear

    :param context: behave.runner.Context
    :param state: true or false (place the same value as in assert)
    :param fail_text: text which will be placed into log if the state is fail
    :return: none
    """
    if not state:
        if not os.path.isdir('Screenshots'):
            os.mkdir('Screenshots')

        scn_name: str = scenario_name(context)
        if not context.browser.save_screenshot('Screenshots/' + scn_name + '.png'):
            print("No screenshot taken\n")
        else:
            print("Screenshot: " + scn_name + ".png taken")

    assert state, fail_text


@step('Taklimakan Network {page} page is opened and start popup is skipped')
def step_impl(context, page):
    """
    This step is used to move on page under test
    :param context: behave.runner.Context
    :param page: related path to the page under test from the main page
      for example: /news
      Special page name only Main in this case main page will be displayed
      If it is not possible to move directly on the page under test it should be
        done in 2-3-4 steps: the first one move to page that available with path and then click on links
    :return: none
    """
    if page == 'Main':
        context.browser.get(context.host)
    else:
        context.browser.get(context.host + page)

    if len(context.browser.find_elements(By.CSS_SELECTOR, "button.btn.btn-buy")) == 1:
        context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").click()
        time.sleep(1)
    else:
        pass


@when('Taklimakan Network web-page is opened')
def step(context):
    """
    Verify that Taklimakan Network main page is reached
    :param context: behave.runner.Context
    :return: none
    """

    verify(context, context.browser.title == "Taklimakan", 'Taklimakan Page is not load successfully')


@then('no exceptions on a page')
def step(context):
    """
    Verify that Symfony not generate any exception and page started successfully
    :param context: behave.runner.Context
    :return: none
    """

    verify(context, requests.get('http://' + os.environ.get('DEPLOY_HOST')).status_code == requests.codes.ok,
           'Taklimakan Page is not load successfully')

    verify(context, re.search(r'[Ee]xception', context.browser.page_source) is None,
           'Taklimakan Page contains some exceptions')
