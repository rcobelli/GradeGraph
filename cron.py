import mysql.connector
import requests
import configparser
import json
import datetime

config = configparser.ConfigParser()
config.read('config.ini')

url = 'https://canvas.instructure.com:443/api/v1/courses?enrollment_type=student&enrollment_state=active&include[]=total_scores&state[]=available'
headers = {'Authorization': ('Bearer ' + config['gg']['api_key'].replace('"', ''))}

r = requests.get(url, headers=headers)
data = json.loads(r.text)

mydb = mysql.connector.connect(
  host="localhost",
  user=config['gg']['cron_username'].strip('\"'),
  passwd=config['gg']['cron_password'].strip('\"'),
  database=config['gg']['db_name'].strip('\"'),
  charset="ascii",
  unix_socket="/var/lib/mysql/mysql.sock"
)

mycursor = mydb.cursor()

for course in data:
    if course['hide_final_grades']:
        grade = 0.0
    else:
        grade = course['enrollments'][0]['computed_current_score']
    query = 'INSERT INTO Grades (course, date, grade) VALUES (%s, %s, %s)'
    data = (course['name'], datetime.datetime.today().strftime('%Y-%m-%d'), grade)
    mycursor.execute(query, data)

mydb.commit()
