from behave import *
from selenium.webdriver.common.by import By
import time
from selenium.webdriver.support import expected_conditions as EC

use_step_matcher("re")

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

@when("I click News button in top menu")
def step_impl(context):
    context.browser.find_element(By.LINK_TEXT, 'News').click()

###THEN###
@then("I should see News index page")
def step_impl(context):
    EC.title_contains('Taklimakan / News')


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