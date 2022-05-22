# (c) 2022/05/22 yoya@awn.jp

import time, json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.chrome import service as fs
import requests
import shutil
from urllib import parse

URL = "https://twitter.com/i/bookmarks"
COOKIE_FILENAME = "twitter-cookie.json"

def url_to_origurl_filename(src):
    up = parse.urlparse(src)
    qs = parse.parse_qs(up.query)
    fmt = qs['format'][0]
    url = "{}://{}{}?format={}&name=orig".format(up.scheme, up.netloc, up.path, fmt)
    filename = "{}.{}".format(up.path.split('/')[-1], fmt);
    return [url, filename]

def download_picture(img_src, filename):
    r = requests.get(img_src, stream = True)
    if r.status_code == 200:
        r.raw.decode_content = True
        with open(filename,'wb') as f:
            shutil.copyfileobj(r.raw, f)
        print('Image sucessfully Downloaded: ',filename)
        return True
    else:
        print('Image Couldn\'t be retreived')
        return False

def download_and_delete(driver, article, imgs):
    for img in imgs:
        img_src = img.get_attribute("src")
        [url, filename] = url_to_origurl_filename(img_src)
        if download_picture(url, filename) == False:
            return
    menus = article.find_elements(By.CSS_SELECTOR, 'div[aria-label="Share Tweet"]')
    if len(menus) != 1:
        print("menu count:{} != 1".format(len(menus)))
        return
    #menus[0].click()
    driver.execute_script('arguments[0].click();', menus[0])
    time.sleep(1)
    remove = article.find_element(By.XPATH, '//span[contains(text(),"Remove Tweet from Bookmarks")]')
    driver.execute_script('arguments[0].click();', remove)
    time.sleep(1)

options = Options()
#options.add_argument('--headless')
driver = webdriver.Chrome(options=options)

driver.get(URL)

json_open = open(COOKIE_FILENAME, 'r')
cookies = json.load(json_open)
 
for cookie in cookies:
    tmp = {"name": cookie["name"], "value": cookie["value"]}
    driver.add_cookie(tmp)
 
driver.get(URL)

while True:
    time.sleep(3)
    wait = WebDriverWait(driver, 10)
    wait.until(EC.presence_of_all_elements_located)
    articles = driver.find_elements(By.CSS_SELECTOR, 'article')
    print("articles count:{}".format(len(articles)))
    if len(articles) < 1:
        break
    article = articles[0]
    imgs = article.find_elements(By.CSS_SELECTOR, 'img[alt="Image"]')
    if len(imgs) > 0:
        print("imgs count:{} > 0".format(len(imgs)))
        download_and_delete(driver, article, imgs)
#    driver.refresh()
