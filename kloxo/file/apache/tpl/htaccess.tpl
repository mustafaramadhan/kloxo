### begin content - please not remove this line

### MR -- using php version different with default php
### 1. Using suphp
### - Copy between '#<FilesMatch \.php$>' to '#</FilesMatch>' and 
###   then remove '#' from '#<FilesMatch', '#</FilesMatch>' and one of '#SetHandler'

#<FilesMatch \.php$>
	#SetHandler x-httpd-php
	#SetHandler x-httpd-php52
	#SetHandler x-httpd-php53
	#SetHandler x-httpd-php54
	#SetHandler x-httpd-php55
	#SetHandler x-httpd-php56
	#SetHandler x-httpd-php70
#</FilesMatch>

### OR

### 2. Using fcgid
### - Copy from '#Options' to '#FCGIWrapper' and 
###   then remove '#' for one of '#FCGIWrapper'

#Options +ExecCGI
#<FilesMatch \.php$>
#	SetHandler fcgid-script
#</FilesMatch>
#FCGIWrapper /usr/bin/php-cgi .php
#FCGIWrapper /usr/bin/php52m-cgi .php
#FCGIWrapper /usr/bin/php53m-cgi .php
#FCGIWrapper /usr/bin/php54m-cgi .php
#FCGIWrapper /usr/bin/php55m-cgi .php
#FCGIWrapper /usr/bin/php56m-cgi .php
#FCGIWrapper /usr/bin/php70m-cgi .php

### end content - please not remove this line
