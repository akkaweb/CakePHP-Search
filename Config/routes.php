<?php
	Router::connect('/search/*', array('controller' => 'search', 'action' => 'index', 'plugin' => 'search'));