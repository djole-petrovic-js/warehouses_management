<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehouseLocationsController extends AppController {
    public $layout = 'main';

    public $uses = [
      'Warehouse',
      'WarehouseLocation',
      'WarehouseLocationType',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $warehouse_id = $this->request->query('warehouse_id');
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('inactive');

      // for select box, id => name
      $formatedWarehouses = $this->Warehouse->getFormatedWarehouses();
      $warehouse_id = $this->request->query('warehouse_id') ?? array_keys($formatedWarehouses)[0];
      $conditions =  ['AND' => ['warehouse_id' => $warehouse_id]];

      $this->request->data['WarehouseLocation'] = $this->request->query;

      if ( $searchQuery ) {
        $conditions['AND']['name LIKE'] = "%{$searchQuery}%";
      }

      if ( $showInactive ) {
        $conditions['AND']['active'] = 0;
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'contain' => ['WarehouseLocationType'],
        'recursive' => -1
      ];

      $this->set([
        'warehouses' => $formatedWarehouses,
        'warehouseLocations' => $this->paginate('WarehouseLocation'),
        'types' => $this->WarehouseLocation->getTypes()
      ]);
    }

    private function _initializeViewData() {
      $this->set([
        'types' => $this->WarehouseLocation->getTypes(),
        'warehouses' => $this->Warehouse->getFormatedWarehouses()
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->WarehouseLocation->id = $id;
        $this->set('updateRequest',true);

        $warehouseLocation = $this->WarehouseLocation->find('first',[
          'conditions' => [
            'id' => $id,
          ],
          'contain' => ['WarehouseLocationType']
        ]);

        if ( !$warehouseLocation ) {
          $this->Flash->set('Magacinsko mesto ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( $this->request->is('get') ) {
          // default selected types
          $this->set('selectedTypes',array_map(function($item){
            return $item['type'];
          },$warehouseLocation['WarehouseLocationType']));
        }

        if ( !$this->request->data ) {
          $this->request->data = $warehouseLocation;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->WarehouseLocation->create();

          if ( $this->WarehouseLocation->addOrEditLocationAndAllTypes($this->request->data) ) {
            $this->Flash->set('Magacinsko mesto je uspesno uneto',[
              'key' => 'successMessage'
            ]);
  
            return $this->redirect(['action' => 'index','?' => [
              'warehouse_id' => $this->request->data['WarehouseLocation']['warehouse_id']
            ]]);
          }
        } else {
          if ( $this->WarehouseLocation->addOrEditLocationAndAllTypes($this->request->data,$id) ) {
            $this->Flash->set('Magacinsko mesto je uspesno azurirano',[
              'key' => 'successMessage'
            ]);

            return $this->redirect(['action' => 'index','?' => [
              'warehouse_id' => $this->request->data['WarehouseLocation']['warehouse_id']
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

      // find warehouse id for clean redirection
      $warehouseID = $this->WarehouseLocation->find('first',[
        'recursive' => -1,
        'conditions' => ['id' => $id]
      ])['WarehouseLocation']['warehouse_id'];

      $result = $this->WarehouseLocation->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Magacinsko mesto je uspesno obrisano',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index','?' => [
        'warehouse_id' => $warehouseID
      ]]);
    }

    private function _findTypeInWarehouse($type,$warehouse) {
      foreach ( $warehouse['WarehouseLocationType'] as $warehouseType ) {
        if ( $warehouseType['type'] === $type ) {
          return $warehouse['WarehouseLocation']['name'] . ' je vec podrazumevana lokacija za tip ' . $type;
        }
      }

      return false;
    }

    public function check_for_default_location() {
      $this->autoRender = false;

      $warehouseLocationID = $this->request->query('warehouse_id');

      $warehouseLocation = $this->WarehouseLocation->find('first',[
        'conditions' => [
          'id' =>  $warehouseLocationID,
        ],
        'contain' => ['WarehouseLocationType']
      ]);

      $types = array_map(function($item){
        return $item['type'];
      },$warehouseLocation['WarehouseLocationType']);

      // // now find the warehouse that have this types, and check if some of them are default
      $warehouseLocations = $this->WarehouseLocation->find('all',[
        'conditions' => [
          'AND' => [
            'default_location' => true,
            'id != ' => $warehouseLocationID
          ]
        ],
        'contain' => ['WarehouseLocationType']
      ]);

      // if there are no default locations, its safe to set a new one
      if ( count($warehouseLocations) === 0 ) {
        return json_encode(['error' => false]);
      }

      $errorMessages = [];

      // now we have default locations, check if some of them are set
      // for the product type that a updating location has
      foreach ( $warehouseLocations as $location ) {
        foreach ( $types as $type ) {
          $errorMessage = $this->_findTypeInWarehouse($type,$location);

          if ( $errorMessage ) {
            $errorMessages[] = $errorMessage;
          }
        }
      }

      if ( $errorMessages ) {
        return json_encode(['error' => true, 'messages' => $errorMessages]);
      }

      return json_encode(['error' => false]);
    }

    public function set_default_location() {
      $this->autoRender = false;

      if ( $this->request->is('post') ) {
        $warehouseLocationID = $this->request->data('id');

        $warehouseLocation = $this->WarehouseLocation->find('first',[
          'conditions' => [
            'id' =>  $warehouseLocationID,
          ],
          'contain' => ['WarehouseLocationType']
        ]);

        $types = array_map(function($item){
          return $item['type'];
        },$warehouseLocation['WarehouseLocationType']);

        // now find the warehouse locations that are defaults for this types.
        $warehouseLocations = $this->WarehouseLocation->find('all',[
          'conditions' => [
            'AND' => [
              'default_location' => true,
              'id != ' => $warehouseLocationID
            ]
          ],
          'contain' => ['WarehouseLocationType']
        ]);

        foreach ( $warehouseLocations as $location ) {
          foreach ( $types as $type ) {
            $errorMessage = $this->_findTypeInWarehouse($type,$location);

            // default location contains the type, so remove its default status
            if ( $errorMessage ) {
              $data = [];

              $this->WarehouseLocation->id = $location['WarehouseLocation']['id'];
              $this->WarehouseLocation->saveField('default_location',0);
            }
          }
        }

        // set a new default location for its types
        $this->WarehouseLocation->id = $warehouseLocationID;
        $this->WarehouseLocation->saveField('default_location','asd');


        return json_encode(['asd' => $types]);
      }
    }
  }