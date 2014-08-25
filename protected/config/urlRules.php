<?php
return array(
	'/' => 'site/index',
	'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
	'<controller:\[a-z]+>/<action:\w+>' => '<controller>/<action>'
);
