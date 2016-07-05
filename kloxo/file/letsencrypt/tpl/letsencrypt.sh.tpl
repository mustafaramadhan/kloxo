<?php
	$req = '';
	$dom = '';

	// MR -- this is for testing
//	$req .= "--staging ";

	$req .= "--rsa-key-size {$key_bits} ";

	if ($emailAddress === 'N/A') {
		$req .= '';
	} else {
		$req .= "--email {$emailAddress} ";
	}

/*
	$san = str_replace(" ", ",", $subjectAltName);

	$dom .= "--domains {$san} ";
*/

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

logdir="/var/log/letsencrypt"
lepath="/etc/letsencrypt/live"
sslpath="/home/kloxo/ssl"
maindom="<?php echo $basedom; ?>"

letsencrypt-auto certonly --agree-tos --text --renew-by-default  \
	--duplicate --webroot --webroot-path /var/run/letsencrypt  \
	<?php echo $req; ?> \
<?php echo $dom; ?> >/dev/null 2>&1

RETVAL=$?

if [ -f ${lepath}/${maindom}/chain.pem ] ; then
	cd ${lepath}/${maindom}

	STAMP=$(date +%Y-%m-%d:%H:%M:%S)
	## MR -- use this cat directly because troube if using like ${slink}
	cat privkey.pem cert.pem chain.pem > all.pem
	merge="cat privkey.pem cert.pem chain.pem > all.pem"
	echo "${STAMP}:Merge with '${merge}'" >> ${logdir}/letsencrypt.log

	for i in privkey.pem cert.pem chain.pem all.pem ; do
		if [ "${i}" == "privkey.pem" ] ; then
			scopy="cp -f${lepath}/${maindom}/privkey.pem ${sslpath}/${maindom}.key"
			cp -f${lepath}/${maindom}/privkey.pem ${sslpath}/${maindom}.key
		elif [ "${i}" == "cert.pem" ] ; then
			scopy="cp -f${lepath}/${maindom}/cert.pem ${sslpath}/${maindom}.crt"
			scopy="cp -f${lepath}/${maindom}/cert.pem ${sslpath}/${maindom}.crt"
		elif [ "${i}" == "chain.pem" ] ; then
			scopy="cp -f${lepath}/${maindom}/chain.pem ${sslpath}/${maindom}.ca"
			cp -f${lepath}/${maindom}/chain.pem ${sslpath}/${maindom}.ca
		elif [ "${i}" == "all.pem" ] ; then
			scopy="cp -f${lepath}/${maindom}/all.pem ${sslpath}/${maindom}.pem"
			cp -f${lepath}/${maindom}/all.pem ${sslpath}/${maindom}.pem
		fi

		STAMP=$(date +%Y-%m-%d:%H:%M:%S)
		echo "${STAMP}:Copy with '${slink}'" >> ${logdir}/letsencrypt.log
	done
fi

exit $RETVAL
