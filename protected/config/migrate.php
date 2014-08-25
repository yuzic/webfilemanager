<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',

	// preloading 'log' component
	'preload'=>array('log'),

	'commandMap'=>array(
		'migrate'=>array(
			'class'=>'system.cli.commands.MigrateCommand',
			'migrationPath'=>'application.migrations',
			'migrationTable'=>'{{migration}}',
			'connectionID'=>'db',
			'templateFile'=>'application.migrations.template',
		),
	),

	// application components
	'components'=>array(
		'db' => array(
			'connectionString' => 'mysql:host=localhost;dbname=elama',
			'emulatePrepare' => true,
			'tablePrefix' => 'yii_',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
			'initSQLs' => array('set time_zone = \'+00:00\';'),
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, trace',
				),
			),
		),
	),
);
