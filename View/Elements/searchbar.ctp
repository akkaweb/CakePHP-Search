<?php
	echo $this->Form->create(null, array(
		'type' => 'get',
		'url' => array('controller' => 'search', 'action' => 'index', 'plugin' => 'search')
	));
	echo $this->Form->input('q', array('label' => false, 'placeholder' => __d('search', 'Search')));
	echo $this->Form->end();
?>