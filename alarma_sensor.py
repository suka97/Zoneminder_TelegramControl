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


### Entradas
# 14, 15
### Salidas Rele
# 4, 22, 6, 26  el 22 no anda
gpio_alarma = LED(4)
sensores = [
    { 'sensor': Button(14), 'monitor_id': '2'},
    { 'sensor': Button(15), 'monitor_id': '3'}
]
gpio_alarma.off(); state = False
start_time = round(time.time())

while True:
    # el sistema esta apagado
    if db_getGlobal('monitor_state') == 'Monitor': 
        gpio_alarma.off(); state = False
        print('off, waiting...')
        time.sleep(2); continue

    # la alarma esta sonando
    if state == True:
        # si estuvo prendida mucho tiempo sin apagarse
        if (round(time.time()) - start_time) > 900:
            gpio_alarma.off(); state = False
            db_setGlobal('alarma_state', 'off')
            print('timeout alarma')
        # si se apago en la base de datos
        if db_getGlobal('alarma_state') == 'off':
            gpio_alarma.off(); state = False
            print('alarma_apagada')
        time.sleep(1)
    # la alarma esta apagada
    else:
        if db_getGlobal('alarma_state') == 'on':
            print('encendido manual')
            gpio_alarma.on(); state = True; start_time = round(time.time())

        for s in sensores:
            if sensorActivo(s['sensor']): 
                print('alarma '+s['monitor_id'])
                gpio_alarma.on(); state = True; start_time = round(time.time())
                db_setGlobal('alarma_state', 'on')
                # send monitor
                os.system('zmu -m '+s['monitor_id']+' -i -U admin -P mellamoandres')
                chats = db_getChats()
                send_success = False
                while(not send_success):
                    msg = 'Alerta movimiento '+s['monitor_id']
                    try:
                        photo_id = tel_sendPhoto('Monitor'+s['monitor_id']+'.jpg', chats[0], msg)
                        for c in chats[1:]: tel_sendPhoto(photo_id, c, msg, no_upload=True)
                        send_success = True
                    except:
                        time.sleep(1)
                        pass
                    
