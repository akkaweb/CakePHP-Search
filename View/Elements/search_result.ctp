<h2>
<?php
	$Model = $this->Search->model($result);
	$data = $result[$Model->name];
	$url = Resources::url($Model->name . '::view', $result);
    echo $this->Html->link($this->Search->highlight($data[$Model->displayField]), $url, array('escape' => false));
?>
</h2>
<?php foreach ($Model->actsAs['Search.Searchable']['fields'] as $field => $score): ?>
	<?php if ($field != $Model->displayField): ?>
		<p><?php echo $this->Search->excerpt($data[$field]); ?></p>
	<?php endif; ?>
<?php endforeach; ?>

<?php echo $this->Html->link(__('read more', true), $url, array('class' => 'more')) ?>