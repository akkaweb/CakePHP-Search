<?php
App::uses('AppHelper', 'View/Helper');

class SearchHelper extends AppHelper {

	public $helpers = array('Text');

	public function beforeRender($viewFile) {
		$this->query = $this->_View->viewVars['query'];
		parent::beforeRender($viewFile);
	}

	public function result($result) {
		$element = 'search_result';
		$underscore = Inflector::underscore($result['model']);

		if (file_exists(APP . 'View' . DS . 'Elements' . DS .'Search' . DS . $underscore . '.ctp')) {
			$element = 'Search' . DS . $underscore;
		}

		return $this->_View->element($element, compact('result'));
	}

	public function highlight($text) {
		return $this->Text->highlight($text, $this->query, array(
			'format' => '<span class="search-highlight">\1</span>',
			'html' => true
		));
	}

	public function excerpt($text, $radius = 100) {
		return $this->highlight($this->Text->excerpt($text, $this->query, $radius));
	}

	public function model() {
		$result = func_get_args(0);
		return ClassRegistry::init($result['model']);
	}

}
