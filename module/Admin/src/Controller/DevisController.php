<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class DevisController extends AbstractActionController {

    private $table = 'BankOrder';
    
    public function indexAction() {
        
        // $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        // $columns = $metadata->getTable($this->table)->getColumns();
        $page  = 1;
        $nb    = 10;
        $start = ($page * $nb) - $nb;
        
        $order = '';
        if($_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $insert = $this->db->query("INSERT INTO {$this->table} (sa_name, created, updated, user_id)
                VALUES (:name, :created, :updated, :user_id)", array(
                'name'    => $request->getPost('option'),
                'created' => time(),
                'updated' => time(),
                'user_id' => $this->user->id,
                 ));
        }

        $stmt = $this->db
                ->createStatement('
                    SELECT bo.id, u.username, count, bo.price, bo.created, bo.payment, bo.hide
                    FROM '.$this->table.' bo
                    LEFT JOIN users u ON u.id=user_id
                    ORDER BY bo.updated '.$sort.'
                    LIMIT :start, 10
                    ');

        $entities = $stmt->execute(array('start'=>$start))
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);

        return array(
            'entities' => $entities,
            // 'columns'  => $columns
        );

    }

    public function showAction() {
        $id = $this->params('id');
        $entity = $this->db->query('SELECT *
            FROM '.$this->table.'
            WHERE id=:id', array('id' => $id)
            )->current();
        $query  = $this->db->query('SELECT b.*
                    FROM BasketOrder b
                    WHERE b.bankorder_id=:id ORDER BY b.id')
                    ->execute(array(
                        'id' => $id,
                        ));
        $baskets = array();
        foreach($query as $key=>$basket) {
            $product = $this->db->query('SELECT * FROM product WHERE id=:id')
                    ->execute(array('id'=>$basket['product_id']))
                    ->current();
            $baskets[$key] = $basket;
            $baskets[$key]['image_path'] = $product['image_path'];
            $baskets[$key]['image_name'] = $product['image_name'];
            $baskets[$key]['price'] = $product['price'];
            $baskets[$key]['name']  = $product['name'];
            $total                 += $product['price'];
        }
        return array(
            'baskets' => $baskets,
            'entity' => $entity
        );
    }

    public function newAction() {
        
        return array(
            'form' => new \Admin\Form\BillForm()
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = new \Admin\Form\BillForm();

        if ($request->isPost()) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            $inputFilter->add($factory->createInput(array(
                'name'     => 'price',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
            $form->setInputFilter($inputFilter);
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert = $this->db->query("INSERT INTO BankOrder (description, count, created, updated, user_id, price, hide, payment)
                    VALUES (:description, :count, :created, :updated, :user_id, :price, :hide, :payment)", array(
                    'description' => $request->getPost('description'),
                    'created' => time(),
                    'updated' => time(),
                    'count'   => 0,
                    'user_id' => $this->user->id,
                    'price'   => $request->getPost('price'),
                    'hide'    => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'payment' => ($request->getPost('hide') == 'on') ? 1 : 0
                     ));
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'devis',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/devis/new');
        return $vm;
    }

    public function editAction() {

        $id = $this->params('id');
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table)
                ->execute()
                ->current();

        if (empty($entity)) {
            die($this->table.' not found.');
        }

        return array(
            'form' => new \Admin\Form\BillForm(),
            'entity' => $entity
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db->query('SELECT p.* FROM Project p WHERE p.user_id=:user and p.id=:id')->execute(array('id' => $id, 'user' => $this->user->id))->current();

        if (empty($entity)) {
            die('Project not found.');
        }

        $form = new \Admin\Form\ProjectForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {

                $update = $this->db->query(
                    'UPDATE Project SET name=:name, description=:description, updated=:updated, comment=:comment, hide=:hide WHERE id=:id', array(
                    'name' => $request->getPost('name'),
                    'description' => $request->getPost('description'),
                    'updated' => time(),
                    'comment' => ($request->getPost('comment') == 'on') ? 1 : 0,
                    'hide' => ($request->getPost('hide') == 'on') ? 1 : 0,
                    'id' => $id
                        ));
                if ($update) {
                    return $this->redirect()->toRoute('admin', array(
                                'controller' => 'project',
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }
        return array(
            'form' => $form
        );
    }

    public function deleteAction($id) {
        $request = $this->getRequest();

        if ($form->isValid()) {

            $this->view->entity = $this->db->query('SELECT p.* FROM Project p WHERE p.user_id=:user and p.id=:id', array('id' => $id, 'user' => $this->user->id))->fetch();

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Project entity.');
            }

            $em->remove($entity);
        }

        return $this->redirect($this->generateUrl('project'));
    }

}
