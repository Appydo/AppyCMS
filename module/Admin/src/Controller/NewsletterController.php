<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class NewsletterController extends AbstractActionController {

    private $table      = 'Newsletter';
    private $controller = 'Newsletter';
    private $id         = 'newsletter_id';
    private $module     = 'admin';
    
    public function send($id) {
        $website = $_SERVER['HTTP_HOST'];
        $result = array();
        $result['newsletter']  = $this->db->query('SELECT * FROM Newsletter n WHERE n.newsletter_id=:id')
                    ->execute(array(
                        'id' => $id,
                        )
                    )->current();
        $result['emails']  = $this->db->query('SELECT u.email FROM users u WHERE u.newsletter=1')
                    ->execute(array(
                        // 'id' => $id,
                        ));
        foreach($result['emails'] as $email) {
            $this->mail($email['email'], '['.$website.'] Votre nouveau compte', $result['newsletter']['newsletter_email']);
        }
        return $result;
    }
    
    public function indexAction() {
        $this->layout('immobilier-en-reseau.fr/admin');
        // $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        // $columns = $metadata->getTable($this->table)->getColumns();
        if (isset($_GET['page']))
            $page = $_GET['page'];
        else
            $page  = 1;
        if (isset($_GET['move'])) {
            if ($_GET['move']=='next') {
                $page++;
            } elseif ($_GET['move']=='prev' and $page!=1) {
                $page--;
            }
        }
        if (empty($page)) $page = 0;

        $nb    = 10;
        $start = ($page * $nb) - $nb;
        if (isset($_GET['order']))
            $order = $_GET['order'];
        else
            $order = '';
        if(isset($_GET['sort']) and $_GET['sort']=='ASC')
            $sort  = 'ASC';
        else
            $sort  = 'DESC';
        
        if (!empty($order)) $order_string = 'ORDER BY '.$order.' '.$sort;
        else $order_string = '';

        $where_string = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('query') != '') {
                $where = array();
                $query = $request->getPost('query');
                $metadata = new \Zend\Db\Metadata\Metadata($this->db);
                $columns  = $metadata->getTable($this->table)->getColumns();
                foreach($columns as $column) {
                    // echo $column->getDataType();
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
                } elseif ($request->getPost('action_select') == 'send') {
                    foreach ($request->getPost('action') as $action) {
                        $result = $this->send($action);
                    }
                }
            }
        }

        $stmt = $this->db
                ->createStatement('
                    SELECT newsletter_id, newsletter_title as title, created, hide
                    FROM '.$this->table.' '.$where_string.' '.$order_string.'
                    ORDER BY newsletter_id DESC
                    LIMIT '.$start.','.$nb);

        $entities = $stmt->execute(array())
                ->getResource()
                ->fetchAll(\PDO::FETCH_ASSOC);

        return array(
            'entities' => $entities,
            'controller' => $this->controller,
            'id' => $this->id,
            'thead' => true, // display thead
            'module' => $this->module,
            'order' => $order,
            'sort' => $sort,
            'query' => $request->getPost('query'),
            'page' => $page,
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
    
    private function generateForm() {
        $metadata = new \Zend\Db\Metadata\Metadata($this->db);
        // $tableNames = $metadata->getTableNames();
        $columns = $metadata->getTable($this->table)->getColumns();
        
        $form = new Form($this->controller);
        foreach($columns as $column) {
            if($column->getName()=='newsletter_email') {
                $element = new Element($column->getName());
                $element->setLabel('Description');
                $element->setAttributes(array(
                    'type'  => 'textarea'
                ));
                $form->add($element);
            } elseif($column->getName()=='newsletter_title') {
                $element = new Element($column->getName());
                $element->setLabel('Titre');
                $element->setValue('Newsletter '.date('d/m/Y H:i'));
                $element->setAttributes(array(
                    'type'  => 'text'
                ));
                $form->add($element);
            } elseif($column->getName()=='hide') {
                $element = new Element\Checkbox($column->getName());
                $element->setLabel('Cacher');
                $form->add($element);
            } elseif(!in_array($column->getName(), array('created','updated'))) {
                $element = new Element($column->getName());
                $element->setLabel($column->getName());
                $element->setAttributes(array(
                    'type'  => 'text'
                ));
                $form->add($element);
            }
        }
        $csrf = new Element\Csrf('csrf');
        $form->add($csrf);
        
        $send = new Element('submit');
        $send->setValue('Submit');
        $send->setAttributes(array(
            'type'  => 'submit'
        ));
        $form->add($send);
        return $form;
    }

    public function newAction() {

        return array(
            'form' => $this->generateForm(),
            'controller' => $this->controller,
            'id' => $this->id
        );
    }

    public function createAction() {
        $request = $this->getRequest();
        $form = $this->generateForm();

        // if (!empty($_FILES)) {var_dump($_FILES);exit();}
        
        if ($request->isPost()) {

            $form->setData($request->getPost());
            if ($form->isValid()) {
                $insert_args = array();
                foreach($form as $element) {
                    if(!in_array($element->getName(),array($this->id, 'csrf', 'submit')))
                        $insert_args[$element->getName()] = $request->getPost($element->getName());
                }
                $insert_args['project_id'] = $this->project['id'];
                $insert_args['created'] = time();
                $insert_args['updated'] = time();

                $insert = $this->db->query(
                    "INSERT INTO ".$this->table." (".implode(",", array_keys($insert_args)).")
                    VALUES (:".implode(",:", array_keys($insert_args)).")",
                        $insert_args
                        );
                
                if ($insert) {
                    $id = $this->db->getDriver()->getLastGeneratedValue();
                    return $this->redirect()->toRoute($this->module, array(
                                'controller' => $this->controller,
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form,
                    'controller' => $this->controller,
                ));
        $vm->setTemplate('admin/'.$this->controller.'/new');
        return $vm;
    }

    public function editAction() {

        $id = $this->params('id');
        $form = $this->generateForm();
        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
                ->execute(array('id'=>$id))
                ->current();
        $form->setData($entity);
        if (empty($entity)) {
            die($this->table.' not found.');
        }

        return array(
            'form' => $form,
            'module' => $this->module,
            'entity' => $entity,
            'controller' => $this->controller,
            'id' => $this->id,
        );
    }

    public function updateAction() {

        $request = $this->getRequest();
        $id = $this->params('id');

        $entity = $this->db
                ->query('SELECT * FROM '.$this->table.' WHERE '.$this->id.'=:id')
                ->execute(array('id'=>$id))
                ->current();

        if (empty($entity)) {
            die('Entity not found.');
        }

        $form = $this->generateForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $update_args = array();
                foreach($form as $element) {
                    if(!in_array($element->getName(),array($this->id, 'csrf', 'submit')))
                        $update_args[$element->getName()] = $request->getPost($element->getName());
                }
                $update_set = substr($update_set, 0, -1);
                $update_args['project_id'] = $this->project['id'];
                $update_args['updated'] = time();
                $update_args[$this->id] = $id;
                $update_set = array();
                foreach($update_args as $key=>$value) {
                    $update_set[] = $key . '=:' . $key;
                }

                $update = $this->db->query('UPDATE '.$this->table.'
                    SET '.implode(",", $update_set).'  WHERE '.$this->id.'=:'.$this->id,
                        $update_args
                        );
                if ($update) {
                    return $this->redirect()->toRoute($this->module, array(
                                'controller' => $this->controller,
                                'action' => 'edit',
                                'id' => $id
                            ));
                }
            }
        }

        $vm = new ViewModel(array(
                    'form' => $form
                ));
        $vm->setTemplate('admin/'.$this->controller.'/edit');
        return $vm;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            foreach($request->getPost('action') as $action) {
                $this->db
                        ->query('DELETE FROM '.$this->table.' WHERE '.$this->id.'=:id')
                        ->execute(array('id' => $action));
            }
        }

        return $this->redirect()->toRoute($this->module, array(
                                'controller' => $this->controller,
                            ));;
    }
    
    public function mail($to, $subject, $html) {
        
        $textPart = new MimePart(strip_tags($html));
        $textPart->type = "text/plain";

        $htmlPart = new MimePart($html);
        $htmlPart->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array($htmlPart, $textPart));

        $message = new Message();
        $message->setBody($body);
        $message->setEncoding('UTF-8');
        $message->addFrom('contact@breizhadonf.com', 'Breizhadonf.com');
        $message->addTo($to);
        $message->setSubject($subject);

        $message->getHeaders()->get('content-type')->setType('multipart/alternative');

        $transport = new \Zend\Mail\Transport\Sendmail();

        try {
            $transport->send($message);
        } catch (Exception $exc) {
            return false;
        }

    }

}
