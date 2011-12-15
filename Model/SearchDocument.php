<?php
App::uses('AppModel', 'Model');
App::uses('Sanitize', 'Utility');

/**
 * SearchDocument Model
 *
 */
class SearchDocument extends AppModel {

	public function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {
		$conditions = $this->_searchConditions($conditions);

		$results = $this->find('all', array(
       		'limit' => $limit,
			'page' => $page,
       		'conditions' => $conditions,
			'order' => array('SearchDocument.order desc', 'total_score desc'),
			'fields' => array('SearchDocument.key', 'SUM(score) as total_score', 'GROUP_CONCAT(field) as fields'),
			'group' => array('SearchDocument.key'),
		));

		foreach ($results as $i => $result) {
			list($model, $primaryKey) = explode('-', $result['SearchDocument']['key'], 2);
			$Model = ClassRegistry::init($model);
			$results[$i] += $Model->find('first', array(
				'conditions' => array('cdbid' => $primaryKey),
				'recursive' => -1,
			));
			$results[$i]['fields'] = explode(',', $result[0]['fields']);
			$results[$i]['model'] = $model;
		}

		return $results;
	}

	public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$conditions = $this->_searchConditions($conditions);

		return $this->find('count', array(
       		'conditions' => $conditions,
			'fields' => 'DISTINCT SearchDocument.key',
		));
	}

	protected function _searchConditions($conditions) {
		$length = strlen($conditions['query']);
		$query = Sanitize::escape($conditions['query']);

		$conditions = array(
			'locale' => 'nl',
		);

		if ($length < 4) {
			$conditions[] = "SearchDocument.data LIKE '%$query%'";
		} else {
			$conditions[] = "MATCH(SearchDocument.data) AGAINST('$query*' IN BOOLEAN MODE)";
		}

		return $conditions;
	}

	public function build($document) {
		$data = array();
		$delete = array();

		$document['key'] = $document['model'] . '-' . $document['id'];

		foreach ($document['fields'] as $field => $options) {
			$value = strip_tags(html_entity_decode($options['value'], ENT_COMPAT, 'UTF-8'));
			$value = Sanitize::escape($value);

			if (!$value) {
				$delete[] = $field;
			} else {
				$data[] = array(
					$document['key'],
					$document['model'],
					$document['locale'],
					$field,
					$value,
					$options['score'],
					$document['order'],
				);
			}
		}

		if ($data) {
			$columns = array('key', 'model', 'locale', 'field', 'data', 'score', 'order');
			$this->_upsert($this->table, $columns, $data, 'data=VALUES(data), score=VALUES(score)');
		}

		if ($delete) {
			$conditions = array(
				'key' => $document['key'],
				'locale' => $document['locale'],
				'field' => $delete,
			);
			$this->deleteAll($conditions);
		}
	}

	public function destroy($document) {
		$conditions = array(
			'key' => $document['model'] . '-' . $document['id'],
			'locale' => $document['locale'],
		);
		$this->deleteAll($conditions);
	}

	protected function _upsert($table, $columns, $data, $update) {
		$columns = '(`' . implode('`, `', $columns) . '`)';

		$values = '';
		foreach ($data as $_data) {
			$values .= "('" . implode("', '", $_data) . "'), ";
		}
		$values = rtrim($values, ", ");

		$sql = "INSERT INTO $table $columns VALUES $values
				ON DUPLICATE KEY UPDATE $update";

		$this->query($sql);
	}

}
