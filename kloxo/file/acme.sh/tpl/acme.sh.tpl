<?php
	$req = '';
	$dom = '';

	// MR -- this is for testing
//	$req .= "--staging ";

	$req .= "--keylength {$key_bits} ";

	if ($emailAddress === 'N/A') {
		$req .= '';
	} else {
		$req .= "--accountemail {$emailAddress} ";
	}

	$doms = explode(" ", $subjectAltName);

	$count = 1;

	foreach ($doms as $k => $v) {
		if ($count === 1) {
			$basedom = $v;
		}

		$dom .= "\t--domain $v  \\\n";

		$count++;
	}
?>
#!/bin/sh

logdir="/var/log/acme.sh"
rootpath="/root/.acme.sh"
sslpath="/home/kloxo/ssl"
maindom="<?php echo $basedom; ?>"

if [ -f ${rootpath}/${maindom}/ca.cer ] ; then
	action="--force --renew"
else
	action="--issue"
fi

${rootpath}/acme.sh ${action} --webroot /var/run/letsencrypt \
<?php echo $dom; ?>
	<?php echo $req; ?> >> /dev/null \
	&> ${logdir}/acme.sh_tmp.log

RETVAL=$?

cat ${logdir}/acme.sh_tmp.log >> ${logdir}/acme.sh.log
'rm' -f ${logdir}/acme.sh_tmp.log

if [ ${RETVAL} -eq 0 ] ; then
	if [ -f ${rootpath}/${maindom}/ca.cer ] ; then
		cd ${rootpath}/${maindom}

		merge="cat ${maindom}.key ${maindom}.cer ca.cer > ${maindom}.pem"
		echo "[$(date)] Merge with '${merge}'" >> ${logdir}/acme.sh.log
		cat ${maindom}.key ${maindom}.cer ca.cer > ${maindom}.pem

		for i in .ca .crt .key .pem ; do
			if [ "${i}" == ".ca" ] ; then
				scopy="cp -f ${rootpath}/${maindom}/ca.cer ${sslpath}/${maindom}${i}"
				cp -f ${rootpath}/${maindom}/ca.cer ${sslpath}/${maindom}${i}
			elif [ "${i}" == ".crt" ] ; then
				scopy="cp -f ${rootpath}/${maindom}/${maindom}.cer ${sslpath}/${maindom}${i}"
				cp -f ${rootpath}/${maindom}/${maindom}.cer ${sslpath}/${maindom}${i}
			else
				scopy="cp -f ${rootpath}/${maindom}/${maindom}${i} ${sslpath}/${maindom}${i}"
				cp -f ${rootpath}/${maindom}/${maindom}${i} ${sslpath}/${maindom}${i}
			fi

			echo "[$(date)] Copy with '${scopy}'" >> ${logdir}/acme.sh.log
		done
	fi
fi

exit ${RETVAL}