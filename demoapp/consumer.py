#!/usr/bin/env python
# -*- coding: utf-8 -*-
import pika, json, time, redis

cache = redis.StrictRedis(host='demoapp_cache', port=6379, db=0)

print(' [*] Subindo consumer')
time.sleep(10)

print(' [*] Tentando conectar no rabbitmq')
# credentials = pika.PlainCredentials('user', 'passwd')
credentials = pika.PlainCredentials('demoapp', 'demoapp')
parameters = pika.ConnectionParameters('demoapp_queue', 5672, '/', credentials)
connection = pika.BlockingConnection(parameters)
channel = connection.channel()

channel.queue_declare(queue='demoapp', durable=True)

def callback(ch, method, properties, body):
    # print(" [x] Received %r" % body)
    jsonbody = json.loads(body)
    print(" [-] Novo e-mail cadastrado: %s" % jsonbody['data']['novo-email'])
    cache.incr('laravel:qtdeSubscribed')

channel.basic_consume(callback, queue='demoapp', no_ack=True)

print(' [*] De pé e aguardando mensagens. CTRL+C para finalizar')
channel.start_consuming()