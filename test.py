TEL_TOKEN = '1727993307:AAGsVtpU3XgswOxGBWNUfSbWRsH_KcQeST8'
TEL_CHAT_ID = '1507660693'


import requests, mysql.connector, os, time
from gpiozero import LED
from gpiozero import Button

def db_getChats():
    cnx = mysql.connector.connect(user='zm', password='mellamoandres', host='localhost', database='telegram_api')
    cursor = cnx.cursor(dictionary=True)
    query = ('SELECT id FROM chats')
    cursor.execute(query)
    salida = []
    for row in cursor: salida.append(row['id'])
    cursor.close()
    cnx.close()
    return salida

def db_setGlobal(id, value):
    cnx = mysql.connector.connect(user='zm', password='mellamoandres', host='localhost', database='telegram_api')
    cursor = cnx.cursor()
    query = ('UPDATE globals SET value="' + value + '" WHERE id LIKE "'+ id +'"')
    cursor.execute(query)
    cnx.commit()
    cursor.close()
    cnx.close()

def db_getGlobal(id):
    cnx = mysql.connector.connect(user='zm', password='mellamoandres', host='localhost', database='telegram_api')
    cursor = cnx.cursor()
    query = ('SELECT * FROM globals WHERE id LIKE "'+id+'"')
    cursor.execute(query)
    salida = cursor.fetchall()[0][1]
    cursor.close()
    cnx.close()
    return salida


def tel_sendMessage(message, chat_id):
    params = {
        'chat_id': chat_id,
        'text': message
    }
    r = requests.post('https://api.telegram.org/bot'+TEL_TOKEN+'/sendMessage', params=params)
    return r.json()

def tel_sendPhoto(img_path, chat_id, caption='', no_upload=False):
    params = {
        'chat_id': chat_id,
        'caption': caption
    }
    files = {}
    if not no_upload: 
        files = { 'photo': open(img_path, 'rb') }
    else:
        params['photo'] = img_path
    r = requests.post('https://api.telegram.org/bot'+TEL_TOKEN+'/sendPhoto', params=params, files=files)
    return r.json()['result']['photo'][0]['file_id']


def sensorActivo(sensor):
    return not sensor.is_pressed


chats = db_getChats()
msg = 'hola'
photo_id = tel_sendPhoto('Monitor2.jpg', chats[0], msg)
for c in chats[1:]: tel_sendPhoto(photo_id, c, msg, no_upload=True)