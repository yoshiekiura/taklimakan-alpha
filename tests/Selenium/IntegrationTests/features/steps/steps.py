import time

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By

use_step_matcher("re")

"""
###GIVEN###
"""

@given("Taklimakan Network is opened and start popup is skipped")
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").click()
    time.sleep(3)


"""
###WHEN###
"""
@when('I click See all news on the main page')
def step_impl(context):
    # TODO this step is not working
    # context.browser.find_element(By.LINK_TEXT, 'See all news').click()
    all_news_link: WebElement = context.browser.find_element_by_link_text('See all news')
    print(all_news_link)
    context.browser.execute_script("arguments[0].scrollIntoView();", all_news_link)
    all_news_link.click()

@when("I click News button in top menu")
def step_impl(context):
    context.browser.find_element(By.LINK_TEXT, 'News').click()


###THEN###
@then("I should see News index page")
def step_impl(context):
    EC.title_contains('Taklimakan / News')
    # assert context.browser.title == 'Taklimakan / News'


@when("I click See all Courses and Articles on the main page")
def step_impl(context):
    """
    :type context: behave.runner.Context
    """
    pass


@then("I should see Courses index page")
def step_impl(context):
    """
    :type context: behave.runner.Context
    """
    pass


@when("I click Education button in top menu")
def step_impl(context):
    """
    :type context: behave.runner.Context
    """
    pass
