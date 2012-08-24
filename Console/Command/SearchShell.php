<?php

class SearchShell extends AppShell {
	
	public $tasks = array('Search.List', 'Search.Build');

    public function main() {
		$this->out('[L]ist searchable models');
		$this->out('[B]uild search index');
		$this->out('[Q]uit');

		$task = strtoupper($this->in('What would you like to do?', array('L', 'B', 'Q')));
		switch ($task) {
			case 'L':
				$this->List->execute();
				break;
			case 'B':
				$this->Build->execute();
				break;
			case 'Q':
				break;
		}
    }
	
	public function getOptionParser() {
	    $parser = parent::getOptionParser();
		
		$parser->addSubcommand('list', array(
		    'help' => 'List searchable models.',
		));
		
		$parser->addSubcommand('build', array(
		    'help' => 'Build search index.',
//			'parser' => $this->Build->getOptionParser()
		));
		
		return $parser;
	}
}