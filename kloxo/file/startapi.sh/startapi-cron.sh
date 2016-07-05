#!/usr/bin/env bash

/root/.startapi.sh/startapi.sh --cron --home "/root/.startapi.sh" >>/var/log/startapi.sh/startapi.sh.log

ssl_key=$(dir -l /root/.startapi.sh/*/*.key 2>/dev/null|awk '{print $9}'|tr '\n' ' ')

for i in ${ssl_key[*]} ; do
	base_name=$(basename $i)
	path_name=${i%$base_name}
	dom_name=${base_name%.key}

	cat ${path_name}${dom_name}.key ${path_name}${dom_name}.cer ${path_name}ca.cer > ${path_name}${dom_name}.pem
done