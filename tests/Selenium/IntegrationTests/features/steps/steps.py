import time
import os
import requests

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support.expected_conditions import staleness_of

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
    old_page = context.browser.find_element_by_tag_name('html')

    if page == 'Main':
        context.browser.get(context.host)
    else:
        context.browser.get(context.host + page)

    # After Deploy it takes an additional time to load the fist page and cache data. That is why need some time to
    #   prevent unexpected fail set to True in before_all hook
    # context.first_time_execution
    WebDriverWait(context.browser, 10).until(staleness_of(old_page))

    if len(context.browser.find_elements(By.CSS_SELECTOR, "button.btn.btn-buy")) == 1:
        if context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").is_displayed():
            context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").click()
            time.sleep(1)
        else:
            pass
    else:
        pass



@given('The course {course_name} exists')
def step_impl(context, course_name):
    """
    To verify the course for all tests is available on first page
    :param context: behave.runner.Context
    :param course_name: name of the course to search for
    :return: none
    """
    try:
        context.browser.find_element(By.LINK_TEXT, course_name), 'The course was not found'
    except AssertionError:
        create_screenshot(context)
        raise


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
    This step is used to click on buttons. To be refactored soon.
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
    elif button == 'Price':
        button = "//A[@href='#price'][text()='Price']"
        context.browser.find_element(By.XPATH, button).click()
    elif button == 'Volatility':
        button = "//A[@href='#volatility'][text()='Volatility']"
        context.browser.find_element(By.XPATH, button).click()
    elif button == 'Alpha':
        button = "//A[@href='#alpha'][text()='Alpha']"
        context.browser.find_element(By.XPATH, button).click()
    elif button == 'Beta':
        button = "//A[@href='#beta'][text()='Beta']"
        context.browser.find_element(By.XPATH, button).click()
    elif button == 'Sharpe ratio':
        button = "//A[@href='#sharpe'][text()='Sharpe Ratio']"
        context.browser.find_element(By.XPATH, button).click()
    elif button == 'Start Course':
        context.browser.find_element(By.CSS_SELECTOR, 'a.btn.btn-buy.btn-block').click()
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


@when('I select {currencies} pair for charts')
def step_impl(context, currencies):
    """
    This step is used to select currencies pair on Analytics page for charts
    :param context: behave.runner.Context
    :param currencies: string from step with currencies
    :return: none
    """
    dropdown = context.browser.find_element(By.CSS_SELECTOR, 'button#dropdown_coins')
    context.browser.execute_script("arguments[0].scrollIntoView();", dropdown)
    dropdown.click()
    context.browser.find_element(By.XPATH, "//*[@value='" + currencies + "']").click()


@when('I open {course_name} course from courses index page')
def step_impl(context, course_name):
    """
    This step is used to open the exact course by its name
    :param context: behave.runner.Context
    :param course_name: string to indicate the course to open in By.LINK_TEXT
    :return: none
    """
    context.browser.find_element(By.LINK_TEXT, course_name).click()


@when('I click Share in {network_name} button')
def step_impl(context, network_name):
    """
    This step is used to share the content in networks
    :param context: behave.runner.Context
    :param network_name: twitter, facebook, telegram, whatsapp, linkedin, slack
    :return: none
    """
    context.browser.find_element(By.XPATH, "//SPAN[@class='at-label'][text()='" + network_name + "']").click()


@when('I login in Twitter with test account')
def step_impl(context):
    """
    This step is used to login in twitter with pre-created account for tests
    :param context: behave.runner.Context
    :return: none
    """
    twitter_username_field = context.browser.find_element(By.CSS_SELECTOR, 'div.row.user')
    twitter_username_field.clear()
    twitter_username_field.send_keys('usetest')
#TODO: continue when twitter registration is fixed


@when('I login with Disqus test account')
def step_impl(context):
    """
    This step is used to login in Disqus with pre-created account for tests
    :param context: behave.runner.Context
    :return: none
    """
    time.sleep(4) #to let the disqus load

    #switch to disqus iframe
    iframe = context.browser.find_element(By.XPATH, "//iframe[@title='Disqus']")
    context.browser.switch_to.frame(iframe)

    context.browser.find_element(By.CSS_SELECTOR, 'span.dropdown-toggle-wrapper').click()
    context.browser.find_element(By.LINK_TEXT, 'Disqus').click()
    context.browser.switch_to.window(context.browser.window_handles[-1])
    username_field = context.browser.find_element(By.NAME, 'username')
    username_field.clear()
    username_field.send_keys('usetest@yopmail.com')
    password_field = context.browser.find_element(By.NAME, 'password')
    password_field.clear()
    password_field.send_keys('freestyle11')
    context.browser.find_element(By.CSS_SELECTOR, 'button#auth-form-button').click()
    time.sleep(4) #let the disqus log you in
    context.browser.switch_to.window(context.browser.window_handles[0])
    context.browser.switch_to.default_content()


@when("I post a Disqus comment '{comment_text}'")
def step_impl(context, comment_text):
    """
    This step is used to post a comment with disqus
    :param context: behave.runner.Context
    :param comment_text: text to insert in comment field
    :return: none
    """
    # switch to disqus iframe
    iframe = context.browser.find_element(By.XPATH, "//iframe[@title='Disqus']")
    context.browser.switch_to.frame(iframe)

    text_field = context.browser.find_element(By.CSS_SELECTOR, 'div.textarea')
    text_field.clear()
    text_field.send_keys(comment_text)

    context.browser.find_element(By.CSS_SELECTOR, 'button.btn.post-action__button').click()
    time.sleep(3)
    context.browser.switch_to.default_content()


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
    This step is used to
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


@then('I should see {chart} chart in the URL')
def step_impl(context, chart):
    """
    This step should verify the chart name is in URL
    :param context: behave.runner.Context
    :param chart: string of a page current URL for assertion
    :return none
    """
    try:
        assert context.host+'/charts/all#'+chart == context.browser.current_url, 'URL does not macth with ' + context.browser.current_url
    except AssertionError:
        create_screenshot(context)
        raise


@then('I should see {pair} pair on analytics page')
def step_impl(context, pair):
    """
    This step should verify the pair of currencies for charts on Analytics page
    :param context: behave.runner.Context
    :param pair: a pair of cryptocurrencies to assert
    :return none
    """
    try:
        assert context.host+'/charts/all?pair=' + pair == context.browser.current_url, \
            'URL does not macth with ' + context.browser.current_url
        dropdown = context.browser.find_element(By.CSS_SELECTOR, 'button#dropdown_coins')
        context.browser.execute_script("arguments[0].scrollIntoView();", dropdown)
        assert dropdown.text == pair, 'Dropdown state does not match ' + pair
    except AssertionError:
        create_screenshot(context)
        raise


@then('I should see {course_name} course view page')
def step_impl(context, course_name):
    """
    This step should verify the course name
    :param context: behave.runner.Context
    :param course_name: name of the course that is displayed in URL
    :return none
    """
    try:
        assert course_name in context.browser.find_element(By.CSS_SELECTOR, 'div.news-header').text, \
            'Course name is not found in header'
        course_name = course_name.lower()
        course_name = course_name.replace(' ', '-')
        assert course_name in context.browser.current_url, 'Course is not loaded in ' + context.browser.current_url
    except AssertionError:
        create_screenshot(context)
        raise


@then('I should see {page_name} page of {course_name} course')
def step_impl(context, page_name, course_name):
    """
    This step should verify the lecture's name and course name on the lecture's page
    :param context: behave.runner.Context
    :param course_name: name of the course that is displayed in title
    :param page_name: name of page
    :return none
    """
    try:
        assert page_name in context.browser.title, page_name + ' was not found in ' + context.browser.title
        assert course_name in context.browser.find_element(By.CSS_SELECTOR, 'div.news-header').text, \
            'Course name is not found in header'
    except AssertionError:
        create_screenshot(context)
        raise


@then('I should see social network {network_name} popup')
def step_impl(context, network_name):
    """
    This step should verify the social network popup has actually appeared
    :param context: behave.runner.Context
    :param network_name: twitter, facebook, linkedin, slack, telegram, whatsapp
    :return none
    """
    network_name = network_name.lower()
    try:
        context.browser.switch_to.window(context.browser.window_handles[-1])
        assert network_name in context.browser.current_url, 'Social network ' + network_name + \
                                                            'wasnt found in ' + context.browser.current_url
    except AssertionError:
        create_screenshot(context)
        raise


@then('I should see PDF loaded')
def step_impl(context):
    """
    This step should verify the pdf with the docs is loaded
    :param context: behave.runner.Context
    :return none
    """
    try:
        context.browser.switch_to.window(context.browser.window_handles[-1])
        assert (True != (('not be found' in context.browser.page_source))), 'Errors in loading PDF'
    except AssertionError:
        create_screenshot(context)
        raise


@then("I should see my Disqus comment '{comment_text}' published")
def step_impl(context, comment_text):
    """
    This step should verify the comment is published via disqus
    :param context: behave.runner.Context
    :param comment_text: text to verify
    :return none
    """

    # switch to disqus iframe
    iframe = context.browser.find_element(By.XPATH, "//iframe[@title='Disqus']")
    context.browser.switch_to.frame(iframe)

    try:
        assert comment_text in context.browser.find_element(By.CSS_SELECTOR, 'div.post-message').text, comment_text+ ' was not found'
    except AssertionError:
        create_screenshot(context)
        raise