<?php
/**
 * Task to list all searchable models
 */
class ListTask extends Shell {
	
	public function execute() {
		$models = $this->models();
		
		$table = array(
			'headers' => array(
				'Model', 'Publishable',
			),
			'rows' => array(),
		);
		foreach ($models as $model) {
			$table['rows'][] = array($model->name, $model->publishable);
		}
		
		$this->_table($table);
	}
	
	/**
	 * Returns all searchable models.
	 *
	 * @return array
	 */
	public function models() {
		// Make sure the models aren't translated.
		Language::$useTranslations = false;
		
		// App models
		$_models = App::objects('Model', null, false);
		
		if ($this->args) {
			$_models = array_intersect($_models, $this->args);
		}

		// Filter non-searchable models
		$models = array();
		foreach ($_models as $model) {
			$model = ClassRegistry::init($model);
			if ($model->Behaviors->attached('Searchable')) {
				$models[] = $model;
			}
		}

		return $models;
	}
	
	protected function _table($table) {
		$this->_row($table['headers']);
		$this->hr();
		foreach ($table['rows'] as $row) {
			$this->_row($row);
		}
		$this->out();
	}
	protected function _row($row) {
		foreach ($row as $i => $column) {
			$row[$i] = str_pad($column, 30);	
		}
		$this->out(implode(' | ', $row));
	}
	
}