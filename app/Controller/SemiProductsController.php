<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class SemiProductsController extends AppController {
    public $layout = 'main';

    public $uses = [
      'Item',
      'MeasurementUnit',
      'ItemType',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    private function _initViewData() {
      $this->set([
        'measurementUnits' => $this->MeasurementUnit->getFormatedData(),
        'itemTypes' => $this->ItemType->getFormatedData(['type_class' => 'semi_product']),
        'showWeightField' => true,
        'statuses' => $this->Item->getStatuses(),
      ]);
    }

    public function index() {
      // Set conditions in where clause if search query or show inactive flag are present
      $conditions = ['AND' => ['ItemType.type_class' => 'semi_product']];
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('inactive');

      $this->request->data['Item'] = $this->request->query;
  
      if ( $searchQuery ) {
        $conditions['AND']['Item.name LIKE'] = "%$searchQuery%";
      }

      if ( !$showInactive ) {
        $conditions['AND']['deleted'] = 0;
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'contain' => ['SemiProduct','ItemType','MeasurementUnit']
      ];

      $this->set([
        'semiProducts' => $this->paginate('Item'),
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->Item->id = $id;
        $this->set('showInactiveCheckbox',true);
        $this->set('updateRequest',true);

        $item = $this->Item->find('first',[
          'conditions' => [
            'Item.id' => $id
          ],
          'contain' => [
            'SemiProduct'
          ]
        ]);

        if ( !$item ) {
          $this->Flash->set('Poluproizvod ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $item;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->Item->create();
        } else {
          $this->request->data['Item']['id'] = $id;
          $this->request->data['SemiProduct']['id'] = $id;
        }

        if ( $this->Item->saveAssociated($this->request->data) ) {
          $this->Flash->set('Poluproizvod je uspesno sacuvan',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }

      $this->_initViewData();
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result =  $this->Item->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Poluproizvod je uspesno obrisan',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }