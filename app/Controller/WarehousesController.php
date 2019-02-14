<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehousesController extends AppController {
    public $layout = 'main';

    public $uses = [
      'Warehouse',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $searchQuery = $this->request->query('search');
      $conditions = [];

      if ( $searchQuery ) {
        $conditions['name LIKE'] = "%{$searchQuery}%";
      }

      $this->request->data['Warehouse'] = $this->request->query;

      $this->set([
        'warehouses' => $this->Warehouse->find('all',[
          'conditions' => $conditions
        ])
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->Warehouse->id = $id;
        $this->set('updateRequest',true);

        $warehouse = $this->Warehouse->findById($id);

        if ( !$warehouse ) {
          $this->Flash->set('Magacin ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $warehouse;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->Warehouse->create();
        } else {
          $this->request->data['Warehouse']['id'] = $id;
        }

        if ( $this->Warehouse->save($this->request->data) ) {
          $this->Flash->set('Magacin je uspesno sacuvan',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result =  $this->Warehouse->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Magacin je uspesno obrisan',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }