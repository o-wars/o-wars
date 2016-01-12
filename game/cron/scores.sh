#!/bin/bash

PATH="/var/www/syscpwebs/larry/o-wars.de/htdocs/game/cron/"
	
pushd $PATH

/usr/bin/php scores.php
/usr/bin/php scores_new.php

popd

