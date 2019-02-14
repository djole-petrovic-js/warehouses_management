<?php

  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehouseLocationAddressesController extends AppController {
    public $layout = 'main';

    public $uses = [
      'WarehouseAddress',
      'WarehouseLocation',
      'WarehouseLocationAddress',
      'Item',
      'WarehouseLocationItem',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('inactive');
      // for select box, id => name
      $formatedWarehousesLocations = $this->WarehouseLocation->getFormatedWarehouses();
      $warehouse_location_id = $this->request->query('warehouse_location_id') ?? array_keys($formatedWarehousesLocations)[0];

      $this->request->data['WarehouseLocation'] = $this->request->query;

      $conditions = ['AND' => ['WarehouseAddress.warehouse_location_id' => $warehouse_location_id]];

      if ( $searchQuery ) {
        $conditions['AND']['Item.name LIKE'] = "%{$searchQuery}%";
      }
      
      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'contain' => [
          'Item','WarehouseAddress'
        ]
      ];

      $this->set([
        'items' => $this->paginate('WarehouseLocationAddress'),
        'warehouses' => $formatedWarehousesLocations
      ]);
    }

    private function _initializeViewData() {
      $this->set([
        'warehouseLocations' => $this->WarehouseLocation->getFormatedWarehouses()
      ]);
    }

    public function save() {
      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->WarehouseLocationAddress->create();

          if ( $this->WarehouseLocationAddress->saveMultipleRecords($this->request->data) ) {
            $this->Flash->set('Magacinski artikal je uspesno unet.',[
              'key' => 'successMessage'
            ]);
            
            return $this->redirect(['action' => 'index', '?' => [
              'warehouse_location_id' => $this->request->data['WarehouseLocationAddress']['warehouse_location_id']
            ]]);
          }
        }
      }

      $this->_initializeViewData();
    }

    public function saveItem($id = null) {
      if ( $id === null ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $this->WarehouseLocationAddress->id = $id;

      if ( $this->request->is('post') ) {
        $this->request->data['WarehouseLocationAddress']['id'] = $id;

        $warehouseItemData = $this->WarehouseLocationAddress->find('first',[
          'conditions' => ['WarehouseLocationAddress.id' => $id],
          'contain' => [
            'WarehouseAddress' => [
              'WarehouseLocation'
            ],
            'Item'
          ]
        ]);

        $warehouseLocationAddress = $this->WarehouseLocationAddress->find('first',[
          'conditions' => ['id' => $id],
          'recursive' => -1
        ]);

        if ( $this->WarehouseLocationAddress->save($this->request->data) ) {
          // if the new quantity is larger than old quantity, increase total quantity
          // else reduce total quantity
          $options = [];

          if ( $warehouseLocationAddress['WarehouseLocationAddress']['quantity'] < $this->request->data['WarehouseLocationAddress']['quantity'] ) {
            $options['increase'] = true;
          } else {
            $options['decrease'] = true;
          }

          $options['difference'] = abs($warehouseItemData['WarehouseLocationAddress']['quantity'] - $this->request->data['WarehouseLocationAddress']['quantity']);

          $issaved = $this->WarehouseLocationItem->saveOrUpdate(
            $warehouseItemData,
            $this->request->data['WarehouseLocationAddress']['quantity'],
            $options
          );

          $this->Flash->set('Kolicina je uspesno sacuvana',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        } 
      }

      $this->set('warehouseLocationAddress',$this->WarehouseLocationAddress->find('first',[
        'conditions' => ['WarehouseLocationAddress.id' => $id],
        'contain' => [
          'Item',
          'WarehouseAddress'
        ]
      ]));
    }
 
    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      // find warehouse location id for clean redirect
      $warehouseLocation = $this->WarehouseLocationAddress->find('first',[
        'conditions' => ['WarehouseLocationAddress.id' => $id],
        'contain' => [
          'WarehouseAddress' => [
            'WarehouseLocation'
          ]
        ]
      ]);

      $result =  $this->WarehouseLocationAddress->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Magacinska adresa je uspesno obrisana',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index','?' => [
        'warehouse_location_id' => $warehouseLocation['WarehouseAddress']['WarehouseLocation']['id']
      ]]);
    }

    public function get_addresses() {
      $this->autoRender = false;

      $warehouse_location_id = $this->request->query('location_id');

      $addresses = $this->WarehouseAddress->find('all',[
        'recursive' => -1,
        'conditions' => ['warehouse_location_id' => $warehouse_location_id]
      ]);

      return json_encode(['success' => true, 'data' => $addresses]);
    }

    public function get_items() {
      $this->autoRender = false;

      $warehouseLocationID = $this->request->query('location_id');
      $search = $this->request->query('search');

      // first find types that this location supports
      $warehouseLocation = $this->WarehouseLocation->find('first',[
        'conditions' => [
          'id' => $warehouseLocationID
        ],
        'contain' => [
          'WarehouseLocationType'
        ]
      ]);

      $OR = array_reduce($warehouseLocation['WarehouseLocationType'],function($acc,$item){
        $acc[] = ['ItemType.type_class' => $item['type']];

        return $acc;
      },[]);

      // now find all articles that have this type
      // and are tangible
      $items = $this->Item->find('all',[
        'limit' => strlen($search) < 1 ? 25 : '',
        'conditions' => [
          'AND' => [
            'ItemType.tangible' => true,
            'Item.name LIKE' => "%{$search}%"
          ],
          'OR' => $OR
        ],
        'contain' => [
          'ItemType',
        ]
      ]);

      return json_encode(['results' => array_map(function($item){
        return ['id' => $item['Item']['id'], 'text' => $item['Item']['name']];
      },$items)]);
    }

    public function validate_products() {
      $this->autoRender = false;

      if ( $this->request->is('post') ) {
        $response = ['messages' => [], 'error' => false];

        foreach ( $this->request->data['ids'] as $itemID ) {
          $item = $this->WarehouseLocationAddress->find('first',[
            'conditions' => [
              'AND' => [
                'warehouse_address_id' => $this->request->data['address_id'],
                'item_id' => $itemID
              ]
            ],
          ]);

          if ( $item ) {
            $response['messages'][] = "{$item['Item']['name']} je vec dodat na ovu lokaciju";
            $response['error'] = true;
          }
        }

        return json_encode($response);
      }
    }
  }