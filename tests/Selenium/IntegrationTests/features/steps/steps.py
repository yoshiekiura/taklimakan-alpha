import time

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By

use_step_matcher("parse")

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

@when(u"I click '{text}' button in top menu")
def step_impl(context, text):
    context.browser.find_element(By.LINK_TEXT, text).click()


###THEN###
#TODO implement correct assertions
@then(u"I should see '{text}' index page")
def step_impl(context, text):
    print(context.browser.title)
    context.assertEqual(context.browser.title, text)


@when("I click See all Courses and Articles on the main page")
def step_impl(context):
    """
    :type context: behave.runner.Context
    """
    pass