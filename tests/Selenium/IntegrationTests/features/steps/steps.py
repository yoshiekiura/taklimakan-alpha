from behave import *
from selenium.webdriver.common.by import By
import time
from selenium.webdriver.support import expected_conditions as EC

use_step_matcher("parse")

###GIVEN###
@given("Taklimakan Network is opened and start popup is skipped")
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").click()
    time.sleep(3)

###WHEN###
#TODO this step is not working
@when('I click See all news on the main page')
def step_impl(context):
    context.browser.find_element(By.LINK_TEXT, 'See all news').click()

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