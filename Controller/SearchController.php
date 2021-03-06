<?php
App::uses('Sanitize', 'Utility');
App::uses('Vertex', 'Menu.Lib');

class SearchController extends AppController {

	public $uses = array('Search.SearchDocument', 'Search.SearchQuery');

	public $components = array('Paginator', 'RequestHandler');

	public $helpers = array(
		'Paginator' => array('className' => 'TwitterBootstrap.BootstrapPaginator'),
		'Form' => array('className' => 'TwitterBootstrap.BootstrapForm'),
		'Html',
		'Search.Search'
	);

	public $viewClass = 'System.Plugin';

	public function index() {
		$query = isset($this->request->query['q']) ? $this->request->query['q'] : null;

		if ($query) {
			// Save searched query
			$this->SearchQuery->save(array('query' => $query, 'locale' => Language::locale()));
		}

		$this->Paginator->settings = array(
			'limit' => Configure::read('Search.limit') ?: 10,
			'conditions' => compact('query'),
			'paramType' => 'querystring',
		);

		$results = $this->Paginator->paginate($this->SearchDocument);

        $this->set(compact('query', 'results'));

		$this->Menus->findCurrentVertex(Resources::url('Search'));

		$main = $this->Menus->get('main');
		if (!$main->current) {
			$vertex = new Vertex(-1, 'search', array(
				'name' => __('Search'),
				'url' => Resources::url('Search'),
			));
			$main->setVertex($vertex);
			$main->currentVertex($vertex);
		}
		if ($query) {
			$vertex = new Vertex($main->current->id, 'search-query', array(
				'name' => $query,
				'url' => false,
			));
			$main->addVertex($vertex);
			$main->currentVertex($vertex);
		}
	}
}
?>
