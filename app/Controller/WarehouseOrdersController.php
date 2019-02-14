<?php

  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehouseOrdersController extends AppController {
    public $layout = 'main';

    public $uses = [
      'User',
      'WarehouseOrder',
      'WarehouseLocation',
      'WarehouseLocationItem',
      'WarehouseLocationAddress',
      'WarehouseAddress',
      'WarehouseOrderItem',
      'WarehousePermission',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function _setInitialViewData() {
      $warehousesForUser = $this->WarehouseLocation->getFormatedWarehousesForUser($this->Auth->user('id'));
      $warehouses = $this->WarehouseLocation->getFormatedWarehouses();

      $warehouses = array_filter($warehouses,function($item) use ($warehousesForUser){
        foreach ( $warehousesForUser as $id => $value) {
          if ( $value === $item ) {
            return false;
          }
        }

        return true;
      });

      $this->set([
        'warehouses' => $warehouses,
        'warehousesForUser' => $this->WarehouseLocation->getFormatedWarehousesForUser($this->Auth->user('id')),
        'transferTypes' => $this->WarehouseOrder->getTransferTypes(),
        'users' => $this->User->getFormatedUsers()
      ]);
    }

    public function beforeFilter() {
      parent::beforeFilter();

      $this->Auth->allow();
    }

    public function getAddresses() {
      $this->autoRender = false;

      // orderid is optinal, it is sent when updating an order
      $orderID = $this->request->query('order_id');

      $warehouseFromID = $this->request->query('warehouse_from_id');
      $itemID = $this->request->query('item_id');
      $transfer_toID = $this->request->query('transfer_from');

      if ( !$warehouseFromID || !$itemID ) {
        return json_encode(['error' => true,'message' => 'Niste prosledili sve podatke']);
      }

      // warehouse_address_issued_id, addreses for issused product
      $warehouseAddresses = $this->WarehouseLocationAddress->find('all',[
        'conditions' => [
          'WarehouseAddress.warehouse_location_id' => $warehouseFromID,
          'WarehouseLocationAddress.item_id' =>  $itemID
        ],
        'contain' => ['WarehouseAddress']
      ]);

      $order = null;

      if ( $orderID ) {
        $order = $this->WarehouseOrder->find('first',[
          'conditions' => ['WarehouseOrder.id' => $orderID]
        ]);
      }

      if ( !$order && !$transfer_toID ) {
        return json_encode(['error' => true,'message' => 'Niste prosledili sve podatke']);
      }

      //warehouse_address_received_id addresses for receiving
      $warehouseAddressesReceiving = $this->WarehouseAddress->find('all',[
        'conditions' => [
          'warehouse_location_id' => $order ? $order['WarehouseOrder']['transfer_to'] : $transfer_toID
        ]
      ]);

      return json_encode([
        'transferFromAddresses' => $warehouseAddresses,
        'transferToAddresses' => $warehouseAddressesReceiving,
      ]);
    }

    // validate the quantity user is trying to assign
    public function validateItem() {
      $this->autoRender = false;

      $itemID = $this->request->query('itemID');
      $quantity_wanted = $this->request->query('quantity_wanted');
      $warehouseLocationID = $this->request->query('warehouseLocationID');

      $item = $this->WarehouseLocationItem->find('first',[
        'conditions' => [
          'warehouse_location_id' => $warehouseLocationID,
          'item_id' => $itemID
        ]
      ]);

      if ( $item['WarehouseLocationItem']['quantity_available'] < $quantity_wanted ) {
        return json_encode(['error' => true, 'message' => 'Raspolozivo stanje je ' . $item['WarehouseLocationItem']['quantity_available']]);
      }

      return json_encode(['success' => true]);
    }

    public function save_items() {
      if ( $this->request->is('post') ) {
        $this->autoRender = false;

        $id = $this->request->data('id');

        if ( !$id ) {
          return json_encode(['error' => true, 'message' => 'Niste odabrali prenosnicu']);
        }

        $warehouseOrder = $this->WarehouseOrder->findById($id);

        if ( !$warehouseOrder ) {
          return json_encode(['error' => true, 'message' => 'Niste odabrali prenosnicu']);
        }

        $itemID = $this->request->data('itemID');
        $quantity_wanted = $this->request->data('quantity_wanted');
        $addressTransferFrom = $this->request->data('addressTransferFromID');
        $addressTransferTo = $this->request->data('addressTransferToID');

        $data = [
          'WarehouseOrder' => $warehouseOrder['WarehouseOrder'],
          'WarehouseOrderItem' => [
            'warehouse_order_id' => $id,
            'item_id' => $itemID,
            'quantity_wanted' => $quantity_wanted,
            'warehouse_address_issued_id' => $addressTransferFrom,
            'warehouse_address_received_id' => $addressTransferTo
          ]
        ];

        // first validate the item
        $this->WarehouseOrderItem->set($data);

        $isValid = $this->WarehouseOrderItem->validates();

        if ( !$this->WarehouseOrderItem->validates() ) {
          return json_encode(['error' => true, 'errorMessages' => $this->WarehouseOrderItem->validationErrors]);
        }

        // try to save the item, if everything is ok, return the new inserted result to the client
        if ( !$this->WarehouseOrderItem->save() ) {
          return json_encode(['error' => true, 'message' => 'Doslo je do greske, pokusajte ponovo']);
        }

        // return this to the client to update the view, and the ID so he can now delete it
        $orderItem = $this->WarehouseOrderItem->find('first',[
          'conditions' => [
            'WarehouseOrderItem.id' => $this->WarehouseOrderItem->getInsertId()
          ],
          'contain' => [
            'WarehouseAdressFrom',
            'WarehouseAdressTo',
            'Item' => 'MeasurementUnit'
          ]
        ]);

        $warehouseLocation = $this->WarehouseAddress->find('first',[
          'conditions' => [
            'WarehouseAddress.id' => $addressTransferFrom
          ],
          'contain' => ['WarehouseLocation']
        ]);

        // reserve the wanted quantity
        $warehouseLocationItem = $this->WarehouseLocationItem->find('first',[
          'conditions' => [
            'item_id' => $itemID,
            'warehouse_location_id' => $warehouseLocation['WarehouseLocation']['id']
          ]
        ]);

        $this->WarehouseLocationItem->id = $warehouseLocationItem['WarehouseLocationItem']['id'];

        $this->WarehouseLocationItem->saveField(
          'quantity_available',
          $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] - $quantity_wanted
        );

        $this->WarehouseLocationItem->saveField(
          'quantity_reserved',
          $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'] + $quantity_wanted
        );

        return json_encode(['id' => $this->WarehouseOrderItem->getLastInsertID(),'success' => true,'orderItem' => $orderItem]);
      }

      return json_encode(['error' => true, 'message' => 'Neispravan zahtev']);
    }

    public function delete_item() {
      $this->autoRender = false;

      if ( $this->request->is('post') ) {
        $id = $this->request->data('id');

        $warehouseOrderItem = $this->WarehouseOrderItem->find('first',[
          'conditions' => [
            'WarehouseOrderItem.id' => $id
          ]
        ]);

        if ( !$id ) {
          return json_encode(['error' => true,'message' => 'Niste odabrali artikal']);
        }

        if ( !$this->WarehouseOrderItem->delete($id) ) {
          return json_encode(['error' => true,'message' => 'Doslo je do greske, pokusajte ponovo']);
        }

        // if everything is ok, just return the reserved quantity to the avaible quantity
        $itemID = $warehouseOrderItem['WarehouseOrderItem']['item_id'];
        $quantity_wanted = $warehouseOrderItem['WarehouseOrderItem']['quantity_wanted'];

        $warehouseLocation = $this->WarehouseAddress->find('first',[
          'conditions' => [
            'WarehouseAddress.id' => $warehouseOrderItem['WarehouseOrderItem']['warehouse_address_issued_id']
          ],
          'contain' => [
            'WarehouseLocation'
          ]
        ]);

        //release the wanted quantity
        $warehouseLocationItem = $this->WarehouseLocationItem->find('first',[
          'conditions' => [
            'item_id' => $itemID,
            'warehouse_location_id' => $warehouseLocation['WarehouseLocation']['id']
          ]
        ]);

        $this->WarehouseLocationItem->id = $warehouseLocationItem['WarehouseLocationItem']['id'];

        $this->WarehouseLocationItem->saveField(
          'quantity_available',
          $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] + $quantity_wanted
        );

        $this->WarehouseLocationItem->saveField(
          'quantity_reserved',
          $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'] - $quantity_wanted
        );

        return json_encode(['success' => true]);
      }

      return json_encode(['error' => true,'message' => 'Neispravan zahtev']);
    }

    public function selected_items() {
      $this->autoRender = false;

      $orderID = $this->request->query('id');

      if ( !$orderID ) {
        return json_encode(['error' => true, 'message' => 'Prenosnica nije prosledjena']);
      }

      $orderItems = $this->WarehouseOrderItem->find('all',[
        'conditions' => [
          'warehouse_order_id' => $orderID
        ],
        'contain' => [
          'WarehouseAdressFrom',
          'WarehouseAdressTo',
          'Item' => 'MeasurementUnit'
        ]
      ]);

      return json_encode(['success' => true, 'data' => $orderItems]);
    }

    public function index() {
      $permissions = $this->WarehousePermission->find('all',[
        'conditions' => [
          'user_id' => $this->Auth->user('id'),
          'permission' => 1
        ]
      ]);

      $warehouseLocationPermission = array_map(function($item){
        return $item['WarehousePermission']['warehouse_location_id'];
      },$permissions);

      $this->set('userPermissions',$warehouseLocationPermission);

      $this->Paginator->settings = [
        'order' => ['created' => 'DESC'],
        'limit' => 25,
        'contain' => [
          'WarehouseFrom',
          'WarehouseTo',
          'User',
          'UserIssued',
          'UserReceived'
        ],
        'conditions' => [
          'OR' => [
            'WarehouseFrom.id' => $warehouseLocationPermission,
            'WarehouseTo.id' => $warehouseLocationPermission
          ]
        ]
      ];

      $this->set([
        'warehouseOrders' => $this->paginate('WarehouseOrder'),
      ]);
    }

    public function save($id = null) {
      // first check if current logged in user has any warehouse permission
      $warehouses = $this->WarehousePermission->find('all',[
        'conditions' => [
          'user_id' => $this->Auth->user('id')
        ]
      ]);

      if ( count($warehouses) === 0 ) {
        $this->Flash->set('Ne postoji ni jedan magacin za koji imate dozvolu da pisete prenosnicu',[
          'key' => 'errorMessage'
        ]);

        return $this->redirect(['action' => 'index']);
      }

      if ( $id != null ) {
        // for editing
        $this->WarehouseOrder->id = $id;
        $this->set('updateRequest',true);

        $warehouseOrder = $this->WarehouseOrder->find('first',[
          'conditions' => [
            'WarehouseOrder.id' => $id,
          ],
        ]);

        if ( !$warehouseOrder ) {
          $this->Flash->set('Prenosnica ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        // check if user has the permission to edit this order
        if ( $warehouseOrder['WarehouseOrder']['created_by_id'] != $this->Auth->user('id') ) {
          $this->Flash->set('Azuriranje ove prenosnice nije moguce',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( $warehouseOrder['WarehouseOrder']['status'] != 'otvoren' ) {
          $this->Flash->set('Azuriranje ove prenosnice nije moguce',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $warehouseOrder;
        }
      } else {
        $this->set('createRequest',true);
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->WarehouseOrder->create();

          // $this->request->data['WarehouseOrder']['status'] = 'otvoren';
          $this->request->data['WarehouseOrder']['created_by_id'] = $this->Auth->user('id');
        } else {
          $this->request->data['WarehouseOrder']['id'] = $id;
        }

        $this->WarehouseOrder->set($this->request->data);

        if ( $this->WarehouseOrder->save() ) {
          $this->Flash->set('Prenosnica je uspesno azurirana',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      } else {
        $this->set('createRequest',true);
      }

      // if brand new order is being created, get only statuses otvoreno and poslato
      if ( !$id ) {
        $this->set('statuses',$this->WarehouseOrder->getStatuses(['otvoren','poslat']));
      } else {
        // other statuses are avaible when updating and based on the current status of the order
        $this->set('statuses',$this->WarehouseOrder->getStatuses());
        // ToDo!!!
      }

      $this->_setInitialViewData();
    }

    /*
      Method for fetching all suported products
    */
    public function get_suported_items() {
      $this->autoRender = false;
      $id = $this->request->query('id');

      $search = $this->request->query('search');
      $warehouseLocationFromID = $this->request->query('warehouse_location_from');
      $warehouseLocationToID = $this->request->query('warehouse_location_to');
      // when creating a new order, client is sending the ids of the articles to exclude
      // when id, those items are excluded automaticly
      $exclude = $this->request->query('exclude');


      // first fetch the warehouse where items are going to be transfered
      // and get its types check what items can be transfered there
      $warehouse = $this->WarehouseOrder->WarehouseTo->find('first',[
        'conditions' => [
          'WarehouseTo.id' => $warehouseLocationToID,
        ],
        'contain' => ['WarehouseLocationType']
      ]);

      // find all items that are located in Transfer From Warehouse Location
      $items = $this->WarehouseLocationItem->find('all',[
        'conditions' => [
          'AND' => [
            'WarehouseLocationItem.warehouse_location_id' => $warehouseLocationFromID,
            'Item.name LIKE ' => "%$search%"
          ]
        ],
        'contain' => [
          'Item' => ['ItemType','MeasurementUnit']
        ]
      ]);

      // find items that are already in the order
      $orderItems = $this->WarehouseOrderItem->find('all',[
        'conditions' => [
          'warehouse_order_id' => $id
        ],
        'contain' => [
          'WarehouseAdressFrom',
          'WarehouseAdressTo',
          'Item' => 'MeasurementUnit'
        ]
      ]);

      // not find all items that have the type that Transfer To Location supports
      // and all that are not already added to the order
      $supportedItems = array_filter($items,function($item) use ($exclude,$warehouse,$orderItems) {
        if ( $exclude ) {
          if ( in_array($item['Item']['id'],$exclude) ) {
            return false;
          }
        }

        $validType = false;
        $itemAlreadyAdded = false;

        foreach ( $warehouse['WarehouseLocationType'] as $type ) {
          if ( $type['type'] === $item['Item']['ItemType']['type_class'] ) {

            $validType = true;

            break;
          }
        }

        if ( !$validType ) return false;

        // now check is it already added
        foreach ( $orderItems as $orderItem ) {
          if ( $orderItem['WarehouseOrderItem']['item_id'] === $item['Item']['id'] ) {
            $itemAlreadyAdded = true;

            break;
          }
        }

        if ( !$itemAlreadyAdded ) return true;

        return false;
      });

      return json_encode(['success' => true, 'data' => $supportedItems]);
    }

    public function delete($id) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result = $this->WarehouseOrder->remove($id,$this->Auth->user('id'));

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Prenosnica uspesno obrisana',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }

    /* Create an order an insert all products */
    public function insert_order_products() {
      if ( $this->request->is('post') ) {
        $this->autoRender = false;

        $this->request->data['WarehouseOrder']['created_by_id'] = $this->Auth->user('id');

        $result = $this->WarehouseOrder->insertOrderAndProducts($this->request->data);

        if ( !$result['error'] ) {
          $this->Flash->set('Prenosnica je uspesno uneta',['key' => 'successMessage']);
        }

        return json_encode($result);
      }

      return json_encode(['error' => true, 'message' => 'Neispravan zahtev']);
    }

    public function sendOrder() {
      $orderID = $this->request->data['WarehouseOrder']['id'] ?? null;

      if ( !$orderID ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index',]);
      }

      try {
        $orderItems = $this->WarehouseOrderItem->find('all',[
          'conditions' => ['warehouse_order_id' => $orderID]
        ]);

        if ( count($orderItems) === 0 ) {
          $this->Flash->set('Niste dodali ni jedan proizvod u prenosnicu',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'save',$orderID]);
        }

        $this->WarehouseOrder->id = $orderID;

        if ( $this->WarehouseOrder->saveField('status','poslat') ) {
          $this->Flash->set('Prenosnica je uspesno poslata',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }

        $this->Flash->set('Doslo je do greske, pokusajte ponovo');

        return $this->redirect(['action' => 'save',$orderID]);
        
      } catch(Exception $e) {
        $this->Flash->set('Doslo je do greske, pokusajte ponovo',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'save',$orderID]);
      }
    }
    /*
      Accept Order Method
    */
    public function acceptOrder($id) {
      if ( $this->request->is('post') ) {
        $this->request->data['User']['id'] = $this->Auth->user('id');

        $result = $this->WarehouseOrder->acceptOrder($id,$this->request->data);

        if ( !$result['error'] ) {
          $this->Flash->set('Prenosnica je uspesno azurirana i spremna je za slanje',[
            'key' => 'successMessage'
          ]);

          return $this->redirect(['action' => 'index']);
        }

        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      }

      $warehouseOrder = $this->WarehouseOrder->find('first',[
        'conditions' => [
          'WarehouseOrder.id' => $id
        ],
        'contain' => [
          'WarehouseOrderItem' => [
            'Item' => [
              'WarehouseLocationItem',
              'MeasurementUnit'
            ]
          ],
          'User',
          'WarehouseFrom',
          'WarehouseTo',
        ]
      ]);

      $this->set([
        'warehouseOrder' => $warehouseOrder
      ]);
    }

    public function details($id) {
      $order = $this->WarehouseOrder->find('first',[
        'conditions' => [
          'WarehouseOrder.id' => $id
        ],
        'contain' => [
          'User',
          'UserIssued',
          'WarehouseFrom' => ['WarehouseAddress'],
          'WarehouseTo',
          'WarehouseOrderItem' => [
            'Item' => ['MeasurementUnit'],
            'WarehouseAdressFrom',
            'WarehouseAdressTo'
          ]
        ]
      ]);

      if ( !$order ) {
        $this->Flash->set('Prenosnica nije pronadjena',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $this->set('order',$order);
    }

    public function completeOrder($id) {
      $orderID = $this->request->data['WarehouseOrder']['id'] ?? null;

      if ( $orderID ) {
        $orderID = $this->request->data['WarehouseOrder']['id'] ?? null;

        if ( !$orderID ) {
          $this->Flash->set('Doslo je do greske, pokusajte ponovo',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'completeOrder']);
        }

        $result = $this->WarehouseOrder->completeOrder($id,$this->Auth->user('id'));

        if ( !$result['error'] ) {
          $this->Flash->set('Roba u prenosnici je uspesno primljena',[
            'key' => 'successMessage'
          ]);

          return $this->redirect(['action' => 'index']);
        }

        $this->Flash->set($result['message'],[
          'key' => 'successMessage'
        ]);
      }

      $order = $this->WarehouseOrder->find('first',[
        'conditions' => [
          'WarehouseOrder.id' => $id
        ],
        'contain' => [
          'User',
          'UserIssued',
          'WarehouseFrom' => ['WarehouseAddress'],
          'WarehouseTo',
          'WarehouseOrderItem' => [
            'Item' => ['MeasurementUnit'],
            'WarehouseAdressFrom',
            'WarehouseAdressTo'
          ]
        ]
      ]);

      if ( !$order ) {
        $this->Flash->set('Prenosnica nije pronadjena',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $this->set('order',$order);
      $this->set('shouldCompleteOrder',true);

      return $this->render('details');
    }

    public function cancelOrder($id) {
      $result = $this->WarehouseOrder->cancelOrder($id,$this->Auth->user('id'));

      if ( $result['error'] ) {
        $this->Flash->set($result['error'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Prenosnica je uspesno otkazana',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }