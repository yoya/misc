# (c) 2022/05/22 yoya@awn.jp

import time, json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.chrome import service as fs

options = Options()
#options.add_argument('--headless')
driver = webdriver.Chrome(options=options)
URL="https://twitter.com/i/bookmarks"

driver.get(URL)
 
json_open = open('twitter-cookie.json', 'r')
cookies = json.load(json_open)
 
for cookie in cookies:
    tmp = {"name": cookie["name"], "value": cookie["value"]}
    driver.add_cookie(tmp)
 
driver.get(URL)
wait = WebDriverWait(driver, 10)

time.sleep(10)

last_img = None

while True:
#    imgs = driver.find_elements(By.TAG_NAME, "img")
    while True:
        imgs = driver.find_elements(By.CSS_SELECTOR, 'img[alt="Image"]')
        if len(imgs) > 0:
            break
        time.sleep(10)
    if last_img != imgs[-1]:
        for img in imgs:
            print(img.get_attribute("src"))
        last_img = imgs[-1]
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight + 10)")
    else:
        tryreload = driver.find_element(By.XPATH, '//span[contains(text(),"Try reloading")]')
        if tryreload is None:
            break
        retry = driver.find_element(By.XPATH, '//span[contains(text(),"Retry")]')
        retry.click()
        time.sleep(5)
    wait = WebDriverWait(driver, 10)
    wait.until(EC.presence_of_all_elements_located)
    time.sleep(5)
