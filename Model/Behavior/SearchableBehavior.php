<?php

class SearchableBehavior extends ModelBehavior {

	/**
	 * SearchDocument instance
	 *
	 * @var Model
	 */
	protected $SearchDocument;

	public function setup($model, $settings = array()) {
		$settings += array(
			'order' => 0,
			'fields' => array(),
		);

		$this->settings[$model->name] = $settings;

		$this->SearchDocument = $this->SearchDocument ?: ClassRegistry::init('Search.SearchDocument');
	}

	protected function _info($model) {
		$info = array();
		$info['model'] = $model->name;
		$info['locale'] = null;
		$info['id'] = $model->id;
		$info['publishable'] = $model->publishable;

		if (preg_match('/(.*)Translation/', $model->name, $match)) {
			$info['model'] = $match[1];

			$foreign_key = $model->belongsTo[$info['model']]['foreignKey'];
			if (isset($model->data[$model->name][$foreign_key])) {
				$info['id'] = $model->data[$model->name][$foreign_key];
			} else {
				return false;
			}

			if (isset($model->data[$model->name]['locale'])) {
				$info['locale'] = $model->data[$model->name]['locale'];
			} else {
				return false;
			}
		}

		if ($model->plugin) {
			$info['model'] = $model->plugin . '.' . $info['model'];
		}

		return $info;
	}

	public function afterSave($model, $created) {
		$settings = $this->settings[$model->name];

		$info = $this->_info($model);
		$fields = $this->_indexFields($model, $info);

		if ($fields === false || $info === false) {
			return true;
		}

		$document = array(
			'fields' => $fields,
			'model' => $info['model'],
			'id' => $info['id'],
			'locale' => $info['locale'],
			'order' => $settings['order'],
		);

		$this->SearchDocument->build($document);

		return true;
	}
	
	protected function _indexFields($model, $info) {
		$data = $model->data[$model->name];
		$settings = $this->settings[$model->name];

		$fields = array();

		if ($info['publishable']) {
			$publishField = is_bool($info['publishable']) ? 'online' : $info['publishable'];
			if (isset($data[$publishField]) && !$data[$publishField]) {
				$this->afterDelete($model);
				return false;
			}
		}

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

	public function afterDelete($model) {
		$info = $this->_info($model);
		unset($info['publishable']);

		if ($info) {
			$this->SearchDocument->destroy($info);
		}
	}
}
?>