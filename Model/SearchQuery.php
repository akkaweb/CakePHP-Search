<?php
App::uses('AppModel', 'Model');
/**
 * SearchQuery Model
 *
 */
class SearchQuery extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'query';

	public $publishable = false;
}
