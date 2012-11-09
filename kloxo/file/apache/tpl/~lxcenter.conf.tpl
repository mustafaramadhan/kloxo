Timeout              150
KeepAlive            <?php echo $keepalive; ?>

MaxKeepAliveRequests 100
KeepAliveTimeout     15

<IfModule prefork.c>
    StartServers        2
    MinSpareServers     <?php echo $minspareservers; ?>

    MaxSpareServers     <?php echo $maxspareservers; ?>

    ServerLimit         <?php echo $maxspareservers; ?>

    MaxClients          <?php echo $maxspareservers; ?>

    MaxRequestsPerChild <?php echo $maxrequestsperchild; ?>

    MaxMemFree          2
    SendBufferSize      65536
    ReceiveBufferSize   65536
</IfModule>

<IfModule itk.c>
    StartServers        2
    MinSpareServers     <?php echo $minspareservers; ?>

    MaxSpareServers     <?php echo $maxspareservers; ?>

    ServerLimit         <?php echo $maxspareservers; ?>

    MaxClients          <?php echo $maxspareservers; ?>

    MaxRequestsPerChild <?php echo $maxrequestsperchild; ?>

    MaxMemFree          2
    SendBufferSize      65536
    ReceiveBufferSize   65536
</IfModule>

<IfModule worker.c>
    StartServers        2
    MaxClients          150
    MinSpareThreads     <?php echo $minsparethreads; ?>

    MaxSpareThreads     <?php echo $maxsparethreads; ?>

    ThreadsPerChild     25
    MaxRequestsPerChild 0
    ThreadStackSize     8196
    MaxMemFree          2
    SendBufferSize      65536
    ReceiveBufferSize   65536
</IfModule>

<IfModule event.c>
    StartServers        2
    MaxClients          150
    MinSpareThreads     <?php echo $minsparethreads; ?>

    MaxSpareThreads     <?php echo $maxsparethreads; ?>

    ThreadsPerChild     25
    MaxRequestsPerChild 0
    ThreadStackSize     8196
    MaxMemFree          2
    SendBufferSize      65536
    ReceiveBufferSize   65536
</IfModule>

Include /home/apache/conf/defaults/*.conf
Include /home/apache/conf/domains/*.conf
Include /home/apache/conf/webmails/*.conf

