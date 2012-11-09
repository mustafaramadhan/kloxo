<?php
	// MR -- reduce maxchildren from 5 to 2 because it's for domain (not for user)
    $maxchildren = '2';
    $maxrequests = '500';
?>
#!/bin/sh
# To use your own php.ini, comment the next line and uncomment the following one
export PHPRC="<?php echo $phpinipath; ?>"
export PHP_FCGI_CHILDREN=<?php echo $maxchildren; ?>

export PHP_FCGI_MAX_REQUESTS=<?php echo $maxrequests; ?>

exec /usr/bin/<?php echo $phpcginame; ?>