<?php

class SearchableBehavior extends ModelBehavior {

	protected $SearchDocument;

	public function setup($model, $settings = array()) {
		$settings += array(
			'order' => 0,
			'fields' => array(),
			'translatable' => $model->Behaviors->enabled('Translatable'),
		);

		$this->settings[$model->name] = $settings;

		$this->SearchDocument = $this->SearchDocument ?: ClassRegistry::init('Search.SearchDocument');
	}

	function afterSave($model, $created) {
		$fields = $this->_indexFields($model);
		$settings = $this->settings[$model->name];

		$document = array(
			'fields' => $fields,
			'model' => $model->name,
			'id' => $model->id,
			'locale' => 'nl',
			'order' => $settings['order'],
		);

		$this->SearchDocument->build($document);

		return true;
	}
	
	protected function _indexFields($model) {
		$data = $model->data[$model->name];
		$settings = $this->settings[$model->name];

		if ($model->publishable && isset($data['online']) && !$data['online']) {
			return false;
		}

		$fields = array();

		foreach ($settings['fields'] as $field => $score) {
		    if (isset($data[$field])) {
		        $fields[$field] = array(
					'value' => $data[$field],
					'score' => $score,
				);
		    }
		}

		if (method_exists($model, 'searchable')) {
			$fields = $model->searchable($fields);
		}

		return $fields;
	}

	function afterDelete($model) {
		$this->SearchDocument->destroy(array('model' => $model->name, 'id' => $model->id, 'locale' => 'nl'));
	}
}
?>