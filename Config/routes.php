<?php
	Resources::connect(
		'Search',
		array(
			'nl' => '/zoeken/*',
			'fr' => '/chercher/*',
			'en' => '/search/*',
		),
		array('controller' => 'search', 'action' => 'index', 'plugin' => 'search')
	);
