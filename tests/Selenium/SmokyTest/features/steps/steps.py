import re
import os
import requests

from behave import *


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
        context.browser.get_screenshot_as_file('screenshot-%s.png' % context.test)

    assert state, fail_text


@when('Taklimakan Network web-page is opened')
def step(context):
    """
    Verify that Taklimakan Network main page is reached
    :param context: behave.runner.Context
    :return: none
    """
    assert context.browser.title == "Taklimakan"


@then('no exceptions on a page')
def step(context):
    """
    Verify that Symfony not generate any exception and page started successfully
    :param context: behave.runner.Context
    :return: none
    """

    verify(context, requests.get('http://'+os.environ.get('DEPLOY_HOST')).status_code == requests.codes.ok,
           'Taklimakan Page is not load successfully')

    verify(context, re.search(r'[Ee]xception', context.browser.page_source) is None,
           'Taklimakan Page contains some exceptions')