import time

from behave import *

from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys

use_step_matcher("parse")

"""
###GIVEN###
"""

@given("Taklimakan Network is opened and start popup is skipped")
def step_impl(context):
    if len(context.browser.find_elements(By.CSS_SELECTOR, "button.btn.btn-buy")) == 1:
        context.browser.find_element(By.CSS_SELECTOR, "button.btn.btn-buy").click()
        time.sleep(1)
    else:
        pass
"""
###WHEN###
"""
@when(u"I click '{link}' on the main page")
def step_impl(context,link):
    all_link: WebElement = context.browser.find_element(By.LINK_TEXT, link)
    context.browser.execute_script("arguments[0].scrollIntoView();", all_link)
    all_link.click()

@when(u"I click '{text}' button in top menu")
def step_impl(context, text):
    context.browser.find_element(By.LINK_TEXT, text).click()

@when("I input my email into subscription form")
def step_impl(context):
    form = context.browser.find_element(By.ID, 'exampleInputEmail1')
    form.clear()
    form.send_keys('test@yopmail.com')

@when("I hit Subscribe button")
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, 'a.btn-sub').click()

"""
###THEN###
"""
@then(u"I should see '{text}' index page")
def step_impl(context, text):
    print(context.browser.title)
    assert text in context.browser.title

@then("I should see Crypto100 chart")
def step_impl(context):
    context.browser.find_element(By.CSS_SELECTOR, 'div#crypto-index-card')

#TODO implement step when subscription in implemented in TKLN
@then("I should see You have been subscribed message")
def step_impl(contect):
    pass