#!/usr/bin/env python

import requests
from scapy.all import *

def toggle_light(src):
	requests.get(url . src)

def arp_display(pkt):
	if ARP in pkt and pkt[ARP].op in (1,2):
	  toggle_light(pkt[ARP].hwsrc)

sniff(prn=arp_display, filter="arp", store=0)
