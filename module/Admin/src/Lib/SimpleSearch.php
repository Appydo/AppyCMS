<?php
namespace Admin\Lib;

class SimpleSearch {
	public function search($where_string='') {
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable($this->table)->getColumns();
                foreach($columns as $column) {
                    if ($column->getDataType()=='text') {
                        $where[] = $column->getName().' LIKE "%'.$query.'%"';
                    }
                }
                if (!empty($where)) $where_string = 'WHERE '.implode(' or ',$where);
                $stmt = $this->db->createStatement('SELECT * FROM '.$this->table);
            } elseif ($request->getPost('action_submit') == '1') {
                if ($request->getPost('action_select') == 'delete') {
                    foreach ($request->getPost('action') as $action) {
                        return $this->deleteAction($action);
                    }
                }
            } elseif ($request->getPost('search_submit')) {
                $result['form']->setData($request->getPost());
                foreach($request->getPost() as $key=>$value) {
                    if($key!='search_submit' and !empty($value))
                        $where[] = $key.'="'.$value.'"';
                }
                if (!empty($where)) $where_string = 'WHERE '.implode(' and ',$where);
            }
        }
	}
}