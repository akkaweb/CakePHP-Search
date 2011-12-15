<div class="row">
	<h1><?php echo __('Search'); ?></h1>

	<?php
		echo $this->Form->create(null, array('type' => 'get'));
		echo $this->Form->input('q', array('label' => false, 'placeholder' => __('Search'), 'default' => $query));
		echo $this->Form->submit(__('Search'));
		echo $this->Form->end();
	?>

	<?php if ($results): ?>
	<?php foreach ($results as $result):  ?>
		<article>
			<?php echo $this->Search->result($result); ?>
		</article>
	<?php endforeach; ?>
	<?php else: ?>
		<?php echo __('No search results'); ?>
	<?php endif; ?>

	<?php
		$this->Paginator->options(array('url' => array('?' => $this->request->query)));
		echo $this->element('pagination', array('model' => 'SearchDocument'), array('plugin' => 'TwitterBootstrap'));
	?>
</div>