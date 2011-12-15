<?php
App::uses('Sanitize', 'Utility');

class SearchController extends AppController {

	public $uses = array('SearchDocument', 'SearchQuery');

	public $components = array('Paginator');

	public $helpers = array(
		'Paginator',
		'Form' => array('className' => 'TwitterBootstrap.BootstrapForm'),
		'Html',
		'Search.Search'
	);

	public $viewClass = 'System.Plugin';

	public function index() {
		$query = isset($this->request->query['q']) ? $this->request->query['q'] : null;

		if ($query) {
			// Save searched query
			$this->SearchQuery->save(array('query' => $query, 'locale' => 'nl'));
		}

		$this->Paginator->settings = array(
			'limit' => 10,
			'conditions' => compact('query'),
			'paramType' => 'querystring',
		);

		$results = $this->Paginator->paginate($this->SearchDocument);

        $this->set(compact('query', 'results'));
	}
}
?>