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
	opener = urllib.FancyURLopener({})
    f = opener.open(appel)
    f.close()

def arp_display(pkt):
	if ARP in pkt and pkt[ARP].op in (1,2):
	  call_jeedom(pkt[ARP].hwsrc)

sniff(prn=arp_display, filter="arp", store=0)
