import time

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.common.by import By

use_step_matcher("parse")

"""
###GIVEN###
"""


@given('Taklimakan Network {page} page is opened and start popup is skipped')
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
        context.browser.get(context.host+page)

    if len(context.browser.find_elements(By.CSS_SELECTOR, "button.btn.btn-buy")) == 1:
        context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").click()
        time.sleep(1)
    else:
        pass


"""
###WHEN###
"""


@when('I click \'{link}\' on the {page} page')
def step_impl(context, link, page):
    """
    Click on link on the page
    :param context: behave.runner.Context
    :param link: link test
    :param page: (not used) this parameter is used only to beautify step and use it in different feature files
    :return: none
    """
    all_link: WebElement = context.browser.find_element(By.LINK_TEXT, link)
    context.browser.execute_script("arguments[0].scrollIntoView();", all_link)
    all_link.click()


@when('I click \'{text}\' button in top menu')
def step_impl(context, text):
    """
    Click on top menu to go to related page
    :param context: behave.runner.Context
    :param text: top menu link text which used to find and click on it
    :return: none
    """
    context.browser.find_element(By.LINK_TEXT, text).click()


@when("I input {email} into subscription form")
def step_impl(context, email):
    """
    This step is used to put e-mail address in form
    It is possible to use this step in such format (to be more flexible and reuse it in different feature files):
    "I input invalid string as email 'non_email_string' into super-puper-interesting form"
    :type email: str
    :param context: behave.runner.Context
    :param email: string which will be place into the form into e-mail field
    :return: none
    """

    form = context.browser.find_element(By.ID, 'exampleInputEmail1')
    form.clear()
    form.send_keys(email)


@when(u'I click on .+ button')
def step_impl(context):
    """
    This step is used to click on CSS_SELECTOR button
    TODO: investigate 'a.btn-sub' is it for all buttons or just only one.
    TODO: if only one - investigate how we could update step regexp to be able to use it for different buttons
    :param context: behave.runner.Context
    :return: none
    """
    context.browser.find_element(By.CSS_SELECTOR, 'a.btn-sub').click()


"""
###THEN###
"""


@then('I should see \'{text}\' page')
def step_impl(context, text):
    """
    This step is used to verify that we reach some page (or leave on the same page) as result of previous steps
    :param context: behave.runner.Context
    :param text: Title of the expected page
    :return: none
    """
    print(text)
    print(context.browser.title)
    assert text in context.browser.title


@then('I should see Crypto100 chart')
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, 'div#crypto-index-card')


# TODO implement step when subscription in implemented in TKLN
@then('I should see You have been subscribed message')
def step_impl(context):
    """
    This step is used to verify that subscription is succeed
    :param context: behave.runner.Context
    :return: none
    """
    pass
