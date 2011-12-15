<?php
App::uses('AppModel', 'Model');
App::uses('Sanitize', 'Utility');

/**
 * SearchDocument Model
 *
 */
class SearchDocument extends AppModel {

	/**
	 * Custom paginate method, used by the paginator component.
	 */
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

		// Insert model data
		foreach ($results as $i => $result) {
			list($model, $primaryKey) = explode('-', $result['SearchDocument']['key'], 2);
			$Model = ClassRegistry::init($model);
			$data = $Model->find('first', array(
				'conditions' => array($Model->primaryKey => $primaryKey),
				'recursive' => -1,
			));
			if ($data) {
				$results[$i] += $data;
				// Fields that contains the query
				$results[$i]['fields'] = explode(',', $result[0]['fields']);
				$results[$i]['model'] = $model;
			} else {
				unset($results[$i]);
			}
		}

		return $results;
	}

	/**
	 * Custom paginateCount method, used by the paginator component.
	 */
	public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$conditions = $this->_searchConditions($conditions);

		return $this->find('count', array(
       		'conditions' => $conditions,
			'fields' => 'DISTINCT SearchDocument.key',
		));
	}

	/**
	 * Returns search conditions.
	 *
	 * @param $conditions array the key 'query' is used as search query
	 * @return array
	 */
	protected function _searchConditions($conditions) {
		$length = strlen($conditions['query']);
		$query = Sanitize::escape($conditions['query']);

		$conditions = array(
			'locale' => Language::locale(),
		);

		if ($length < 4) {
			// Words with length smaller than 4 are not indexed
			$conditions[] = "SearchDocument.data LIKE '%$query%'";
		} else {
			$conditions[] = "MATCH(SearchDocument.data) AGAINST('$query*' IN BOOLEAN MODE)";
		}

		return $conditions;
	}

	/**
	 * Add/update document to index.
	 *
	 * @param $document the document to be indexed
	 */
	public function build($document) {
		$data = array();
		$delete = array();

		$document['key'] = $document['model'] . '-' . $document['id'];

		foreach ($document['fields'] as $field => $options) {
			$value = strip_tags(html_entity_decode($options['value'], ENT_COMPAT, 'UTF-8'));

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
			$this->_destroy($conditions);
		}
	}

	/**
	 * Delete document form index.
	 *
	 * @param $document
	 */
	public function destroy($document) {
		$conditions = array(
			'key' => $document['model'] . '-' . $document['id'],
			'locale' => $document['locale'],
		);
		$this->_destroy($conditions);
	}

	protected function _upsert($table, $columns, $data, $update) {
		$columns = '(`' . implode('`, `', $columns) . '`)';

		$values = array();
		foreach ($data as $_data) {
			$_values = array();
			foreach ($_data as $_value) {
				$_value = Sanitize::escape($_value);
				$_values[] = "'$_value'";
			}
			$_values = '('. implode(', ', $_values) . ')';
			$values[] = $_values;
		}
		$values = implode(', ', $values);

		$sql = "INSERT INTO $table $columns VALUES $values
				ON DUPLICATE KEY UPDATE $update";

		$this->query($sql);
	}

	protected function _destroy($conditions) {
		$table = $this->table;
		$where = array();
		foreach ($conditions as $field => $value) {
			if (is_array($value)) {
				$where[] = "`$field`" . " IN ('" . implode("', '", $value) . "')";
			} else {
				$where[] = "`$field` = '$value'";
			}
		}
		$where = implode(' AND ', $where);
		$sql = "DELETE FROM $table WHERE $where";

		$this->query($sql);
	}

}
