# (c) 2022/05/22 yoya@awn.jp

import io, time, json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
import requests
import shutil, subprocess
from urllib import parse

URL = "https://twitter.com/i/bookmarks"
COOKIE_FILENAME = "twitter-cookie.json"
ESCAPEDFILE = "escapedURL.txt"

escapedFile  = open(ESCAPEDFILE, "a")
if escapedFile == None:
    escapedFile  = open(ESCAPEDFILE, "w")

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
        # subprocess.run(["imgcat-thumb", filename], shell=True, check=True)
        return True
    else:
        print('Image Couldn\'t be retreived')
        return False

def delete_bookmark(driver, article):
    try:
        menus = article.find_elements(By.CSS_SELECTOR, 'div[aria-label="Share Tweet"]')
    except Exception as e:
        print(e)
        driver.refresh()
        return
    if len(menus) != 1:
        print("menu count:{} != 1".format(len(menus)))
        driver.refresh()
        time.sleep(2)
        return
    driver.execute_script('arguments[0].click();', menus[0])
    time.sleep(2)
    try:
        remove = article.find_element(By.XPATH, '//span[contains(text(),"Remove Tweet from Bookmarks")]')
        driver.execute_script('arguments[0].click();', remove)
    except Exception as e:
        print(e)
        driver.refresh()
        return
    time.sleep(1)

def download_and_delete(driver, article, imgs):
    for img in imgs:
        try:
            img_src = img.get_attribute("src")
        except Exception as e:
            print(e)
            return
        [url, filename] = url_to_origurl_filename(img_src)
        if download_picture(url, filename) == False:
            return
    delete_bookmark(driver, article)

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

def scrolling():
    print("scrolling")
    driver.execute_script("window.scrollTo(0, 0)")
    time.sleep(1)
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight)")
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight+10)")
    time.sleep(1)

scrolling()

retry_count = 0

while True:
    time.sleep(3)
#    wait = WebDriverWait(driver, 10)
#    wait.until(EC.presence_of_all_elements_located)
    try:
        retry = driver.find_element(By.XPATH, '//span[contains(text(),"Retry")]')
        if retry is not None:
            retry.click()
            continue
    except Exception as e:
        pass
    articles = driver.find_elements(By.CSS_SELECTOR, 'article')
    print("articles count:{}".format(len(articles)))
    if len(articles) < 7:
        scrolling()
        time.sleep(1)
        if len(articles) < 3:
            retry_count = retry_count + 1
            print("retry_count:{}".format(retry_count))
            if retry_count > 10:  # the end
                break
            if len(articles) == 0:
                print("refresh")
                driver.refresh()
                time.sleep(5)
            continue;
    retry_count = 0
    articles = driver.find_elements(By.CSS_SELECTOR, 'article')
    article = articles[-1]
    try:
        imgs = article.find_elements(By.CSS_SELECTOR, 'img[src*="/media/"]')
    except Exception as e:
        print("refresh (no img tag in dom?)")
        scrolling()
        time.sleep(10)
        continue
    print("imgs count:{}".format(len(imgs)))
    if len(imgs) > 0:
        download_and_delete(driver, article, imgs)
    else:
        print("no img")
        aa = article.find_elements(By.CSS_SELECTOR, 'a')
        for a in aa:
            href = a.get_attribute("href")
            mesg = "no img href: {}\n".format(href)
            print(mesg)
            escapedFile.write(mesg)
            escapedFile.flush()
        delete_bookmark(driver, article)
        time.sleep(10)

escapedFile.close()
driver.close()

print("OK")
