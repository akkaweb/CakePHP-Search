<?php
/**
 * Task to build search index
 */
class BuildTask extends Shell {

	public $tasks = array('Search.List');
	
	public function execute() {
		$this->out('Building index, this may take a few minutes.');
		$models = $this->List->models();
		
		foreach ($models as $model) {
			$this->_buildIndex($model);
		}
	}
	
	protected function _buildIndex($model) {
		$this->out('<info>' . $model->name . '</info>');
	    
		$entities = $model->find('all', array('recursive' => -1));
	
		$success = $fail = 0;
		foreach ($entities as $i => $entity) {
			if ($model->save($entity)) {
				$success+= 1;
			} else {
				$fail += 1;
			}
		}
		
		$this->out("Success: $success, Fail: $fail");
	}
	
}
