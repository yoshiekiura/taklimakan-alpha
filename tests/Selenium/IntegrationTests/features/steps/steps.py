from behave import *
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import os

use_step_matcher("re")

###GIVEN###
@given("Taklimakan Network is opened and start popup is skipped")
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, 'button.btn.btn-buy').click()

###WHEN###
@when('I click See all news on the main page')
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, 'btn btn-success btn-block btn-load').click()

@when("I click News button in top menu")
def step_impl(context):
    context.browser.find_element(By.LINK_TEXT, 'News').click()

###THEN###
@then("I should see News index page")
def step_impl(context):
    print(context.browser.find_element(By.CLASS_NAME, 'div.news-header'))


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