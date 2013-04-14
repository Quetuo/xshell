all:				xshell-compressed

xshell-compressed:	xshell
	./compress xshell.php > xshell.min.php

xshell:				template.o index.php
	./compress-php index.php > xshell.php

template.o:			template.html style.css script.js version.js
	./compress-html template.html > template.o

