#!/bin/sh

/usr/bin/letsencrypt-auto --renew >/dev/null 2>&1

sh /script/fixssl >/dev/null 2>&1