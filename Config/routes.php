<?php
	Resources::connect(
		'Search',
		array(
			'nl' => '/zoeken/*',
			'en' => '/search/*',
		),
		array('controller' => 'search', 'action' => 'index', 'plugin' => 'search')
	);
