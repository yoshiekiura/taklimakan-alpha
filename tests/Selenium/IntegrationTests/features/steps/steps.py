import time
import os
import requests

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.common.by import By

use_step_matcher("parse")

"""
NB!!! All assertions should be done only in @then steps
Whenever it is possible provide readable error messages for assertions

Then step MUST use the following template:

try:
  ...
  assert (some condition), 'MANDATORY assert fail description'
  assert (some condition), 'MANDATORY assert fail description'
  assert (some condition), 'MANDATORY assert fail description'
except AssertionError:
  create_screenshot(context) # this function create and store screenshoot for future analysis
  raise # (!!!) assertion exception MUST be rethrown and it will log message path as second parameter in assert  

"""


# TODO print current url if any step fails (should be in env.py)
def create_screenshot(context):
    """
    This function create screenshot and store it in file with scenario name
    :param context:
    :return:
    """
    # Create screenshot
    if not os.path.isdir('Screenshots'):
        os.mkdir('Screenshots')

    scn_name: str = context.scenario.name.replace(' ', '_')
    if not context.browser.save_screenshot('Screenshots/' + scn_name + '.png'):
        print("No screenshot taken\n")
    else:
        print("Screenshot: " + scn_name + ".png taken")


"""
###GIVEN###
"""


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

    time.sleep(3)

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
    time.sleep(1)


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


@when('I click on {button} button')
def step_impl(context, button):
    """
    This step is used to click on submit buttons
    :param context: behave.runner.Context
    :param button: used to identify the button when CSS_SELECTOR differs
    :return: none
    """
    if button == 'Subscribe':
        button = 'a.btn-sub'
        context.browser.find_element(By.CSS_SELECTOR, button).click()
    elif button == 'Crypto100':
        button = "//STRONG[text()='TN Crypto 100']"
        context.browser.find_element(By.XPATH, button).click()
    else:
        print('Selector for button is not defined')


@when("I fill in registration form {option} wallet")
def step_impl(context, option):
    """
    This step is used fill all the fields in registration form
    :param context: behave.runner.Context
    :param option: can be 'with' or 'without' entering the wallet in reg form
    :return: none
    """
    first_name = context.browser.find_element(By.NAME, 'registration[first_name]')
    last_name = context.browser.find_element(By.NAME, 'registration[last_name]')
    email = context.browser.find_element(By.NAME, 'registration[email]')
    wallet = context.browser.find_element(By.NAME, 'registration[erc20_token]')
    password = context.browser.find_element(By.NAME, 'registration[password][first]')
    password_confirm = context.browser.find_element(By.NAME, 'registration[password][second]')
    first_name.clear()
    first_name.send_keys('Test')
    last_name.clear()
    last_name.send_keys('User')
    email.clear()
    email.send_keys('test_tkln@yopmail.com')
    password.clear()
    password.send_keys('freestyle11')
    password_confirm.clear()
    password_confirm.send_keys('freestyle11')

    if option == 'with':
        wallet.clear()
        wallet.send_keys('0x64d8D5ea88e7525c95E85e533462AD34f43b70AE')
    else:
        pass


@when('I submit the form')
def step_impl(context):
    """
    This step is used submit registration, login and enter code forms
    :param context: behave.runner.Context
    :return: none
    """
    context.browser.find_element(By.CSS_SELECTOR, 'input.btn.btn-buy.btn-block').click()
    time.sleep(2)


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
    try:
        assert requests.get(context.host).status_code == requests.codes.ok, text + ' page is not loaded successfully'

        assert text in context.browser.title, 'Expected Page Title is: \'' + text + '\' actual title is: \'' \
                                              + context.browser.title + '\''
    except AssertionError:
        create_screenshot(context)
        raise


@then('no exceptions on a page')
def step_impl(context):
    """
    Verify that Symfony not generate any exception and page started successfully
    :param context: behave.runner.Context
    :return: none
    """
    try:
        assert (True != (('Exception' in context.browser.page_source) or ('exception' in context.browser.page_source)))
    except AssertionError:
        create_screenshot(context)
        raise


@then('I should see Crypto100 chart')
def step_impl(context):
    """
    This step is used to verify that we have reached the page with charts
    :param context: behave.runner.Context
    :return: none
    """
    try:
        context.browser.find_element(By.CSS_SELECTOR, 'div#crypto-index-card')
    except AssertionError:
        print("Crypto100 chart was not found")
        create_screenshot(context)
        raise


@then('I should see Email Verification form')
def step_impl(context):
    """
    This step is used to verify the email verification form has actually appeared
    :param context: behave.runner.Context
    :return:
    """
    try:
        context.browser.find_element \
            (By.XPATH,
             "(//H5[@class='modal-title reg-title'][text()='Email Verification'][text()='Email Verification'])[1]")
    except AssertionError:
        print("Email ver form was not found")
        create_screenshot(context)
        raise


# TODO implement step when subscription in implemented in TKLN
@then('I should see You have been subscribed message')
def step_impl(context):
    """
    This step is used to verify that subscription is succeed
    :param context: behave.runner.Context
    :return: none
    """
    pass


@then('I should see registration form validation messages')
def step_impl(context):
    """
    This step should verify validation message has appeared
    :param context: behave.runner.Context
    :return: none
    """
    try:
        context.browser.find_element \
            (By.XPATH, "(//SMALL[@class='form-text text-muted valid-error'][text()='Required'][text()='Required'])[1]")
        context.browser.find_element \
            (By.XPATH, "(//SMALL[@class='form-text text-muted valid-error'][text()='Required'][text()='Required'])[2]")
        context.browser.find_element \
            (By.XPATH, "//SMALL[@class='form-text text-muted valid-error'][text()='Enter correct email address']")
        context.browser.find_element(By.XPATH, "//SMALL[@id='passwordHelp']")
    except AssertionError:
        print("Some validation message was not found")
        create_screenshot(context)
        raise


@then('I should see active {chart} chart')
def step_impl(context, chart):
    """
        This step should verify validation message has appeared
        :param context: behave.runner.Context
        :param chart: chart name from the step, string
        :return:
    """
    pass
# TODO implement the step above
