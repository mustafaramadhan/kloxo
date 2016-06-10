<?php

	exec("sh /script/clearcache3");

	$factor = 1;

	// check memory -- $2=total, $3=used, $4=free, $5=shared, $6=buffers, $7=cached
	$total = (int)shell_exec("free -m | grep Mem: | awk '{print $2}'");
	$spare = ($spare) ? $spare : ($total * 0.25);
	$apps  = (int)shell_exec("free -m | grep buffers/cache: | awk '{print $3}'");
	$avail = $total - $spare - $apps;

	if ($select === 'low') {
		$maxpar_p = (int)($avail / 30 * $factor / 4);
		$minpar_p = (int)($maxpar_p / 2);

		$maxpar_w = (int)($avail / 35 * $factor / 4);
		$minpar_w = (int)($maxpar_w / 2);
	} elseif ($select === 'medium') {
		$maxpar_p = (int)($avail / 30 * $factor / 3);
		$minpar_p = (int)($maxpar_p / 2);

		$maxpar_w = (int)($avail / 35 * $factor / 3);
		$minpar_w = (int)($maxpar_w / 2);
	} elseif ($select === 'high') {
		$maxpar_p = (int)($avail / 30 * $factor / 2);
		$minpar_p = (int)($maxpar_p / 2);

		$maxpar_w = (int)($avail / 35 * $factor / 2);
		$minpar_w = (int)($maxpar_w / 2);
	} else {
		$maxpar_p = 4;
		$minpar_p = 2;
		$maxpar_w = 4;
		$minpar_w = 2;
	}

	// correction
	if ($maxpar_p < 4) { $maxpar_p = 4; }
	if ($minpar_p < 2) { $minpar_p = 2; }
	if ($maxpar_w < 4) { $maxpar_w = 4; }
	if ($minpar_w < 2) { $minpar_w = 2; }

	if (!isset($keepalive)) {
		$keepalive = 'Off';
	} else {
		$keepalive = 'On';
	}
	
	$mcfactor = 25;
?>

Timeout 150
KeepAlive <?php echo $keepalive; ?>

MaxKeepAliveRequests 100
KeepAliveTimeout 15

<IfModule prefork.c>
    StartServers 2
    MinSpareServers <?php echo $minpar_p; ?>

    MaxSpareServers <?php echo $maxpar_p; ?>

    ServerLimit <?php echo $maxpar_p; ?>

    <IfVersion >= 2.4>
        MaxRequestWorkers <?php echo $maxpar_p; ?>

        MaxConnectionsPerChild 4000
    </IfVersion>
    <IfVersion < 2.4>
        MaxClients <?php echo $maxpar_p; ?>

        MaxRequestsPerChild 4000
    </IfVersion>
    MaxMemFree 2
    SendBufferSize 65536
    ReceiveBufferSize 65536
</IfModule>

<IfModule itk.c>
    StartServers 2
    MinSpareServers <?php echo $minpar_p; ?>

    MaxSpareServers <?php echo $maxpar_p; ?>

    ServerLimit <?php echo $maxpar_p; ?>

    <IfVersion >= 2.4>
        MaxRequestWorkers <?php echo $maxpar_p; ?>

        MaxConnectionsPerChild 4000
    </IfVersion>
    <IfVersion < 2.4>
        MaxClients <?php echo $maxpar_p; ?>

        MaxRequestsPerChild 4000
    </IfVersion>
    MaxMemFree 2
    SendBufferSize 65536
    ReceiveBufferSize 65536
</IfModule>

<IfModule worker.c>
    StartServers 2
    MinSpareThreads <?php echo $minpar_w; ?>

    MaxSpareThreads <?php echo $maxpar_w; ?>

    ThreadsPerChild 25
    ServerLimit <?php echo $maxpar_w; ?>

    <IfVersion >= 2.4>
        MaxRequestWorkers <?php echo $maxpar_w * $mcfactor; ?>

        MaxConnectionsPerChild 0
    </IfVersion>
    <IfVersion < 2.4>
        MaxClients <?php echo $maxpar_w * $mcfactor; ?>

        MaxRequestsPerChild 0
    </IfVersion>

    SendBufferSize 65536
    ReceiveBufferSize 65536
</IfModule>

<IfModule event.c>
    StartServers 2
    MinSpareThreads <?php echo $minpar_w; ?>

    MaxSpareThreads <?php echo $maxpar_w; ?>

    ThreadsPerChild 25
    ServerLimit <?php echo $maxpar_w; ?>

    MaxRequestsPerChild 0
    <IfVersion >= 2.4>
        MaxRequestWorkers <?php echo $maxpar_w * $mcfactor; ?>

        MaxConnectionsPerChild 0
    </IfVersion>
    <IfVersion < 2.4>
        MaxClients <?php echo $maxpar_w * $mcfactor; ?>

        MaxRequestsPerChild 0
    </IfVersion>

    SendBufferSize 65536
    ReceiveBufferSize 65536
</IfModule>

Include /opt/configs/apache/conf/defaults/*.conf
Include /opt/configs/apache/conf/domains/*.conf

### selected: __optimize__ ###

