<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehouseAddressesController extends AppController {
    public $layout = 'main';

    public $uses = [
      'Warehouse',
      'WarehouseLocation',
      'WarehouseAddress',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    private function _initializeViewData() {
      $this->set([
        'warehouseAddresses' => $this->WarehouseLocation->getFormatedWarehouses()
      ]);
    }

    public function index() {
      $formatedLocations = $this->WarehouseLocation->getFormatedWarehouses();
      $warehouse_location_id = $this->request->query('warehouse_location_id') ?? array_keys($formatedLocations)[0];
      $row = $this->request->query('row');
      $shelf = $this->request->query('shelf');
      $box = $this->request->query('box');

      $this->request->data['WarehouseLocation'] = $this->request->query;

      $conditions =  ['AND' => ['warehouse_location_id' => $warehouse_location_id]];

      if ( $row ) {
        $conditions['AND']['row'] = $row;
      }

      if ( $shelf ) {
        $conditions['AND']['shelf'] = $shelf;
      }

      if ( $box ) {
        $conditions['AND']['box'] = $box;
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'recursive' => -1
      ];

      $this->set([
        'warehouseAddresses' => $this->paginate('WarehouseAddress'),
        'warehouses' => $formatedLocations,
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->WarehouseAddress->id = $id;
        $this->set('updateRequest',true);

        $warehouseAddress = $this->WarehouseAddress->find('first',[
          'recursive' => -1,
          'conditions' => [
            'id' => $id,
          ],
        ]);

        if ( !$warehouseAddress ) {
          $this->Flash->set('Magacinska adresa ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $warehouseAddress;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->WarehouseAddress->create();

          if ( $this->WarehouseAddress->save($this->request->data) ) {
            $this->Flash->set('Magacinska Adresa je uspesno uneta.',[
              'key' => 'successMessage'
            ]);

            return $this->redirect(['action' => 'index', '?' => [
              'warehouse_location_id' => $this->request->data['WarehouseAddress']['warehouse_location_id']
            ]]);
          }
        }
      }

      $this->_initializeViewData();
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      // find warehouse location id for clean redirection
      $warehouseLocationID = $this->WarehouseAddress->find('first',[
        'recursive' => -1,
        'conditions' => ['id' => $id]
      ])['WarehouseAddress']['warehouse_location_id'];

      $result =  $this->WarehouseAddress->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Magacinska adresa je uspesno obrisana',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index','?' => [
        'warehouse_location_id' => $warehouseLocationID
      ]]);
    }
  }