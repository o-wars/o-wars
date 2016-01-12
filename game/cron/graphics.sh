#!/bin/bash

PATH="/var/www/syscpwebs/larry/o-wars.de/htdocs/game/cron"
	
pushd $PATH

/usr/bin/php graphics.php

popd

