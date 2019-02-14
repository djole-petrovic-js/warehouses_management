<?php

  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehousePermissionsController extends AppController {
    public $layout = 'main';

    public $uses = [
      'User',
      'WarehouseLocation',
      'WarehousePermission',
      'AroAcoModel'
    ];

    public function _setInitialViewData() {
      $this->set([
        'warehouseLocations' => $this->WarehouseLocation->getFormatedWarehouses(),
        'users' => $this->User->getFormatedUsers()
      ]);
    }

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $conditions = ['AND' => []];
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('permission');

      $this->request->data['Item'] = $this->request->query;
  
      if ( $searchQuery ) {
        $conditions['AND']['username LIKE'] = "%$searchQuery%";
      }

      if ( !$showInactive ) {
        $conditions['AND']['permission'] = 1;
      }

      $this->request->data['WarehousePermission'] = $this->request->query;

      $this->Paginator->settings = [
        'conditions' => $conditions,
        'limit' => 25,
        'contain' => ['User','WarehouseLocation']
      ];

      $this->set([
        'warehousePermissions' => $this->paginate('WarehousePermission'),
      ]);
    }

    public function save() {
      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->WarehousePermission->create();
        } else {
          $this->request->data['WarehousePermission']['id'] = $id;
        }

        $this->WarehousePermission->create();
        $this->WarehousePermission->set($this->request->data);

        if ( $this->WarehousePermission->save($this->request->data) ) {
          $this->Flash->set('Dozvola za prenos je uspesno dodata',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }

      $this->_setInitialViewData();
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result = $this->WarehousePermission->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Dozvola za prenos je uspesno obrisana',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }

    public function removeOrAddPermission($id,$permission) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $this->WarehousePermission->id = $id;

      if ( !$this->WarehousePermission->saveField('permission',$permission) ) {
        $this->Flash->set('Doslo je do greske, pokusajte ponovo',['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Dozvola za prenos je uspesno oduzeta',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }