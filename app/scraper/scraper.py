import requests
from bs4 import BeautifulSoup
import json
import pymysql
import logging
import os 
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
cookies = []
s = requests.Session()
def getLoginPage():
	url = "http://103.217.84.186/dlgtpl/Login.aspx"
	r = s.get(url)
	return r.text
def login():
	global cookies
	headers={"User-Agent":"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36"}
	settings = loadSettings()
	username = settings['credentials']['username']
	password = settings['credentials']['password']
	url = "http://103.217.84.186/dlgtpl/Login.aspx"
	loginPage = s.get(url)
	cookies = loginPage.cookies.get_dict()
	sessions = extractSession(loginPage.text)
	data = {
		"__EVENTTARGET": "",
		"__EVENTARGUMENT": "",
		"__LASTFOCUS": "",
		"__VIEWSTATE": sessions['__VIEWSTATE'],
		"__VIEWSTATEGENERATOR": sessions['__VIEWSTATEGENERATOR'],
		"__EVENTVALIDATION": sessions['__EVENTVALIDATION'],
		"xTxtBxUserName": username,
		"xTxtBxPassword": password,
		"xBtnLogin.x": 98,
		"xBtnLogin.y": 20
	}
	#Gets the cookies from the server so session can be used in the whole script
	r = s.post(url,data=data,headers=headers)
	c = r.cookies
def searchPage():
	url = "http://103.217.84.186/dlgtpl/Master/mstSubsStbSearchSubscribers.aspx"
	# cookies = {"ASP.NET_SessionId":"faqhnz30xh5z5ev5peyseu55"}
	r = s.get(url,cookies=cookies)
	r = r.text
	soup = BeautifulSoup(r,'html.parser')
	session = soup.findAll('input',{'type':'hidden'})
	sessions = {}
	for ses in session:
		sessions.update({ses['name']: ses['value']})
	return sessions
def extractSession(data):
	r = data
	soup = BeautifulSoup(r,'html.parser')
	session = soup.findAll('input',{'type':'hidden'})
	sessions = {}
	for ses in session:
		sessions.update({ses['name']: ses['value']})
	return sessions
def getPage(page,data):
	url = "http://103.217.84.186/dlgtpl/Master/mstSubsStbSearchSubscribers.aspx"
	# cookies = {"ASP.NET_SessionId":"faqhnz30xh5z5ev5peyseu55"}
	sessions = extractSession(data)
	params = {
		"__EVENTTARGET": "_ctl0$ContentPlaceHolder1$xDGr",
		"__EVENTARGUMENT": "Page$" + str(page),
		"__VIEWSTATE": sessions['__VIEWSTATE'],
		"__VIEWSTATEGENERATOR": sessions['__VIEWSTATEGENERATOR'],
		"__EVENTVALIDATION": sessions['__EVENTVALIDATION'],
	}
	hdr = {
	"user-agent":"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.62 Safari/537.36",
	"Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
	"Accept-Encoding": "gzip, deflate",
	"Connection": "keep-alive",
	"Content-Type": "application/x-www-form-urlencoded",
	"Host": "103.217.84.186",
	"Referer": "http://103.217.84.186/dlgtpl/Master/mstSubsStbSearchSubscribers.aspx",
	"Upgrade-Insecure-Requests": "1"
	}
	r = s.post(url,data=params,cookies=cookies,headers=hdr)
	return r.text
def getRecords():
	url = "http://103.217.84.186/dlgtpl/Master/mstSubsStbSearchSubscribers.aspx"
	sessions = searchPage()
	params = {
		"__EVENTTARGET": "",
		"__EVENTARGUMENT": "",
		"__VIEWSTATE": sessions['__VIEWSTATE'],
		"__VIEWSTATEGENERATOR": sessions['__VIEWSTATEGENERATOR'],
		"__EVENTVALIDATION": sessions['__EVENTVALIDATION'],
		"_ctl0:ContentPlaceHolder1:rdolstSubsSearch": "Subscriber Code",
		"_ctl0:ContentPlaceHolder1:txtSearch": "",
		"_ctl0:ContentPlaceHolder1:btnSearch": "Search",
	}
	hdr = {
	"user-agent":"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.62 Safari/537.36"
	}
	r = s.post(url,data=params,cookies=cookies,headers=hdr)
	return r.text
def getSTBNumber(url):
	# cookies = {"ASP.NET_SessionId":"faqhnz30xh5z5ev5peyseu55"}
	r = s.get(url,cookies=cookies)
	r = r.text
	soup = BeautifulSoup(r,'html.parser')
	tr = soup.findAll('tr',{'class':'iStyle1'})[0]
	td = tr.findAll('td')
	stbNumber = td[1].a.text
	return stbNumber
def getBalaceAmount(stbid,subsid):
	try:
		url = "http://103.217.84.186/dlgtpl/Admin/frmcassubsstatus.aspx?stbid=" + str(stbid) + "&subsid="+ str(subsid) +"&Type=Y&b=0"
		r = s.get(url,cookies=cookies)
		r = r.text
		soup = BeautifulSoup(r,'html.parser')
		tr = soup.findAll('tr',{'class':'iStyle1'})[0]
		td = tr.findAll('td')
		balance_amt = td[-1].text
		return balance_amt
	except:
		return "Not Found"

def loadSettings():
	#Loads the users settings from the file
	dir_path = os.path.dirname(os.path.realpath(__file__)) + '/settings.json'
	data = json.load(open(dir_path))
	return data

def dumpToDatabase(info):
	settings = loadSettings()
	#Connect with database
	connection = pymysql.connect(host=settings['database']['host'],
                             user=settings['database']['username'],
                             password=settings['database']['password'],
                             db=settings['database']['db'],
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
	with connection.cursor() as cursor:
		#Insert data into the database
		sql = "INSERT INTO " + settings['database']['table'] + " (sr_no,subscriber_name,subscriber_code,address,phone_number,setTopBoxNumber,smartCardNumber,blackListStatus,subscriberStatus,stbNumber,subsid,stbid,balance_amt,setTopBoxStatus) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)"
		cursor.execute(sql,
			(info['sr_no'],
				info['subscriber_name'],
				info['subscriber_code'],
				info['address'],
				info['phone_number'],
				info['setTopBoxNumber'],
				info['smartCardNumber'],
				info['blackListStatus'],
				info['subscriberStatus'],
				info['stbNumber'],
				info['subsid'],
				info['stbid'],
				info['balanceAmt'],
				info['setTopBoxStatus'],
				)
			)
		print ("Added " + info['subscriber_name'] + " to database")
		connection.commit()

def checkTable():
	settings = loadSettings()
	connection = pymysql.connect(host=settings['database']['host'],
                             user=settings['database']['username'],
                             password=settings['database']['password'],
                             db=settings['database']['db'],
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
	with connection.cursor() as cursor:
		sql = "SHOW TABLES LIKE '" + settings['database']['table'] + "'"
		cursor.execute(sql)
		results = cursor.fetchone()
		if results:
			return True
		else:
			return False

def createTable():
	settings = loadSettings()
	connection = pymysql.connect(host=settings['database']['host'],
                             user=settings['database']['username'],
                             password=settings['database']['password'],
                             db=settings['database']['db'],
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
	with connection.cursor() as cursor:
		sql = open('table.sql','r').read()
		cursor.execute(sql)
		connection.commit()
	print ("Table Created")


def emptyTable():
	settings = loadSettings()
	#Connect to database
	connection = pymysql.connect(host=settings['database']['host'],
                             user=settings['database']['username'],
                             password=settings['database']['password'],
                             db=settings['database']['db'],
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
	with connection.cursor() as cursor:
		#Delete data from the table
		sql = "TRUNCATE TABLE " + settings['database']['table']
		cursor.execute(sql)
		connection.commit()

def dropTable():
	settings = loadSettings()
	#Connect to database
	connection = pymysql.connect(host=settings['database']['host'],
                             user=settings['database']['username'],
                             password=settings['database']['password'],
                             db=settings['database']['db'],
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
	with connection.cursor() as cursor:
		#Delete data from the table
		sql = "DROP TABLE " + settings['database']['table']
		cursor.execute(sql)
		connection.commit()

def getURLProps(url):
	from urllib.parse import urlparse
	url = url.a['href'].replace('..','')
	url = "http://103.217.84.186/dlgtpl" + url
	o = urlparse(url)
	o = o.query
	o = o.split('&')
	return o

def run():
	tableExists = checkTable()
	if tableExists:
		dropTable()
	tableExists = checkTable()
	#Check if the table exists
	if not tableExists:
		createTable()
	#Empties the current data
	emptyTable()
	#Initialize the session and logs in
	login()
	#Loads the user's settings
	settings = loadSettings()
	#Starts looping through each page starting from 1
	currentPageData = ""
	endOfPages = False
	i = 1
	while not endOfPages:
		print ("-------------------------PAGE" + str(i) + "------------------------------")
		try:
			if currentPageData == "":
				currentPageData = getRecords() #Default first page
			if i > 1:
				records = getPage(i,currentPageData)
			if i == 1:
				base = getRecords() #Used for changing pages
				records = base
			currentPageData = records
			soup = BeautifulSoup(records,'html.parser')
			table = soup.findAll('table',{'class':'gridview','bordercolor':'LightSteelBlue'})
			table = table[0]
			tr = table.findAll('tr')
			#Magic happens down there and get all the relevant information
			for idx,item in enumerate(tr):
				try:
					if idx > 0:
						td = item.findAll('td')
						o = getURLProps(td[2])
						url = td[2].a['href'].replace('..','')
						url = "http://103.217.84.186/dlgtpl" + url
						information = {
							"sr_no": td[0].text.replace('\n','').replace(' ','').replace('.',''),
							"subscriber_name": td[1].text,
							"subscriber_code": td[2].a.text,
							"address": td[3].text,
							"phone_number": td[4].text,
							"setTopBoxNumber": td[5].text,
							"smartCardNumber": td[6].text,
							"setTopBoxStatus": td[7].text,
							"blackListStatus": td[8].text,
							"subscriberStatus": td[9].text,
							"stbid": o[1].split('=')[1],
							"subsid": o[0].split('=')[1],
							"stbNumber": getSTBNumber(url),
							"balanceAmt": getBalaceAmount(o[1].split('=')[1],o[0].split('=')[1])
						}
						dumpToDatabase(information)
				except Exception as e:
					print ("")
		except Exception as e:
			print ("End of pages")
			endOfPages = True
		i += 1

run()