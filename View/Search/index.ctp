<h1><?php echo __d('search', 'Search'); ?></h1>

<?php
	echo $this->Form->create(null, array('type' => 'get'));
	echo $this->Form->input('q', array('label' => false, 'placeholder' => __d('search', 'Search'), 'default' => $query));
	echo $this->Form->submit(__d('search', 'Search'));
	echo $this->Form->end();
?>

<?php if ($results): ?>
<?php foreach ($results as $result):  ?>
	<article>
		<?php echo $this->Search->result($result); ?>
	</article>
<?php endforeach; ?>
<?php else: ?>
	<?php echo __d('search', 'No search results'); ?>
<?php endif; ?>

<?php
	$this->Paginator->options(array('url' => array('?' => $this->request->query)));
	echo $this->Paginator->pagination();
?>