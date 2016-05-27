#!/usr/bin/env python
import urllib
import requests
from scapy.all import *

try:
	jeedom=sys.argv[1]
except :
	print 'Il faut donner un jeedom'
	exit(3)

def call_jeedom(src):
	appel = '{jeedom}&type=dashbutton&uid={uid}'.format(jeedom=jeedom, uid=src)
	#print appel
	print src
	opener = urllib.FancyURLopener({})
	f = opener.open(appel)
	f.close()

from scapy.all import *
import os

def arp_display(pkt):
  if pkt[ARP].op == 1: #who-has (request)
    if pkt[ARP].psrc == '0.0.0.0': # ARP Probe
      if pkt[ARP].hwsrc == '74:75:48:41:67:9r': # Tide button
        print "You pushed Tide button"
      elif pkt[ARP].hwsrc == '11:aa:60:00:4d:f2': # Elements
        print "You Pushed Huggies button"
      else:
        print "ARP Probe from unknown device: " + pkt[ARP].hwsrc

print sniff(prn=arp_display, filter="arp", store=0, count=1000)
