import mysql.connector
from gpiozero import LED
from time import sleep


def getAlarmaState():
    cnx = mysql.connector.connect(user='zm', password='mellamoandres', host='localhost', database='telegram_api')
    cursor = cnx.cursor()
    query = ('SELECT * FROM globals WHERE id LIKE "alarma_state"')
    cursor.execute(query)
    salida = cursor.fetchall()[0][1] == 'on'
    cursor.close()
    cnx.close()
    return salida


sleep(60)

gpio_alarma = LED(17)
gpio_alarma.off()
while True:
    if getAlarmaState():
        # print('on')
        gpio_alarma.on()
    else:
        # print('off')
        gpio_alarma.off()
    sleep(15)
