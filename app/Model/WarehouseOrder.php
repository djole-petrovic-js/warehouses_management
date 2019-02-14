<?php

  App::uses('Model', 'Model');

  class WarehouseOrder extends Model {
    public $actsAs = ['Containable'];

    public $belongsTo = [
      'User' => [
        'type' => 'INNER',
        'foreignKey' => 'created_by_id'
      ],
      'UserIssued' => [
        'className' => 'User',
        'foreignKey' => 'issued_by',
      ],
      'UserReceived' => [
        'className' => 'User',
        'foreignKey' => 'received_by',
      ],
      'WarehouseFrom' => [
        'className' => 'WarehouseLocation',
        'foreignKey' => 'transfer_from'
      ],
      'WarehouseTo' => [
        'className' => 'WarehouseLocation',
        'foreignKey' => 'transfer_to'
      ],
    ];

    public $hasMany = [
      'WarehouseOrderItem' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_order_id'
      ]
    ];

    private $statuses = [
      'otvoren' => 'otvoren',
      'poslat' => 'poslat',
      'spreman' => 'spreman',
      'isporucen' => 'isporucen',
      'otkazan' => 'otkazan',
    ];

    private $transferTypes = [
      'standard' => 'standard',
      'trebovanje' => 'trebovanje'
    ];

    /**
     * Get all types that can be assigned to an order
     * @return Array ['value' => 'label']
     */
    public function getTransferTypes() {
      return $this->transferTypes;
    }


    /**
     * Get all statuses that can be assigned to an order
     * @param [optional] $statuses, types that are requested
     * @return Array ['value' => 'label']
     */
    public function getStatuses($statuses = []) {
      // if all statuses are requested, statuses arr will be empty, so return them all
      if ( count($statuses) === 0 ) {
        return $this->statuses;
      }

      // not gather all statuses that are requested
      $output = [];

      foreach ( $this->statuses as $key => $value ) {
        if ( in_array($value,$statuses) ) {
          $output[$key] = $value;
        }
      }

      return $output;
    }
    
    public $validate = [
      'code' => [
        'isUnique' => [
          'rule' => 'isUnique',
          'message' => 'Doslo je do greske, sifra prenosnice vec postoji'
        ]
      ],
      'transfer_from' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Magacinsko mesto iz kog se prenosi je obavezno',
        ],
        'checkIfWarehouseLocationCanBeChanged' => [
          'rule' => 'checkIfWarehouseLocationCanBeChanged'
        ]
      ],
      'transfer_to' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Magacinsko mesto u koje se roba prenosi je obavezno',
        ],
      ],
      'status' => [
        'validateStatus' => [
          'rule' => 'validateStatus',
          'message' => 'Niste odabrali status'
        ]
      ],
      'type' => [
        'validateType' => [
          'rule' => 'validateType',
          'message' => 'Niste odabrali tip'
        ]
      ],
      'work_order' => [
        'validateWorkOrder' => [
          'rule' => 'validateWorkOrder',
          'message' => 'Radni nalog je obavezan kad je tip prenosa trebovanje',
        ]
      ],
      'issued_by' => [
        'validateIssuedBy' => [
          'rule' => 'validateIssuedBy',
          'message' => 'Polje "Robu izdao" je obavezno kad je status spremno i isporucen'
        ]
      ],
      'received_by' => [
        'validateReceivedBy' => [
          'rule' => 'validateReceivedBy',
          'message' => 'Polje "Robu Primio" je obavezno kad je status isporucen'
        ]
      ]
    ];

    public function beforeValidate($options = []) {
      if( !isset($this->data['WarehouseOrder']['code']) && !isset($this->data['WarehouseOrder']['type']) ){
        if ( !isset($this->data['WarehouseOrder']['type']) ) return true;
        $year = date('Y');
        $count = str_pad($this->find('count') + 1,4,'0',STR_PAD_LEFT);
  
        $lastID = $this->find('first',[
          'order' => ['id' => 'DESC'],
          'recursive' => -1
        ]);
  
        $id = $lastID ? $lastID['WarehouseOrder']['id'] : 1;
  
        $this->data['WarehouseOrder']['code'] = $prefix . $year . str_pad(($count + 1),4,0,STR_PAD_LEFT);
      }

      return true;
    }


    /**
     * Check if warehouse locations in the order can be changed
     * They can if there are no items added in the order
     * @param [optional] $statuses, types that are requested
     * @return boolean
     */
    public function checkIfWarehouseLocationCanBeChanged($value) {
      if ( isset($this->data['WarehouseOrder']['id']) ) {
        $order = $this->findById($this->data['WarehouseOrder']['id']);

        if ( !$order ) {
          return 'Doslo je do greske, pokusajte ponovo';
        }

        // if user is not updating warehouse locations, validation passed
        if ( 
          $order['WarehouseOrder']['transfer_from'] == $this->data['WarehouseOrder']['transfer_from'] &&
          $order['WarehouseOrder']['transfer_to'] == $this->data['WarehouseOrder']['transfer_to']
        ) {
          return true;
        }

        // check if the order has any articles added
        $orderItems = $this->WarehouseOrderItem->find('first',[
          'conditions' => [
            'warehouse_order_id' => $order['WarehouseOrder']['id']
          ]
        ]);

        // if any item is found, user cannot change warehouse locations
        if ( $orderItems ) {
          return 'Nije moguce menjati magacinska mesta ukoliko ste dodali proizvode';
        }

        return true;
      }

      return true;
    }

    public function validateStatus($value) {
      return array_key_exists($value['status'],$this->statuses);
    }

    public function validateType($value) {
      return array_key_exists($value['type'],$this->transferTypes);
    }

    public function validateReceivedBy($value) {
      //received_by
      // if status is not isporuceno or spremno, and the value is empty
      // its ok to return true
      if ( !in_array($this->data['WarehouseOrder']['status'],['isporucen']) ) {
        if ( empty($value['received_by']) ) {
          return true;
        }
      }

      // not the value must not be empty
      if ( empty($value['received_by']) ) {
        return false;
      }

      return true;
    }

    public function validateIssuedBy($value) {
      // if status is not isporuceno or spremno, and the value is empty
      // its ok to return true
      if ( !in_array($this->data['WarehouseOrder']['status'],['isporucen','spreman']) ) {
        if ( empty($value['issued_by']) ) {
          return true;
        }
      }

      // not the value must not be empty
      if ( empty($value['issued_by']) ) {
        return false;
      }

      return true;
    }

    public function validateWorkOrder($value) {
      // if status is not trebovanje, and if a value is empty, its ok
      // otherwise, check entered data
      if ( $this->data['WarehouseOrder']['type'] !== 'trebovanje' ) {
        if ( empty($value['work_order']) ) {
          return true;
        }
      }

      if ( empty($value['work_order']) ) {
        return false;
      }

      return true;
    }

    /**
     * Insert new order and items associated with it
     * @param $data mixed
     * @return ['error' => boolean, 'validationErrors' => string[]]
     */
    public function insertOrderAndProducts($data) {
      $dataSource = $this->getDataSource();
      $result = ['error' => true,'validationErrors' => []];
      // ToDo : find model in associations, dont load it
      $WarehouseLocationItem = ClassRegistry::init('WarehouseLocationItem');

      try {
        $dataSource->begin();

        // first try to save the order
        if ( !$this->save($data) ) {
          $dataSource->rollback();

          $result['validationErrors'] = $this->validationErrors;

          return $result;
        }

        $orderID = $this->getLastInsertID();

        // now get the new inserted orded, it is needed for validation in WarehouseOrderItem model
        $order = $this->find('first',[
          'conditions' => [
            'WarehouseOrder.id' => $orderID
          ]
        ]);

        if ( isset($data['items']) && count($data['items']) ) {
          // now for every item in the order, set the order id, and insert all
          foreach ( $data['items'] as $item ) {
            $item['WarehouseOrder'] = $order['WarehouseOrder'];
            $item['WarehouseOrderItem']['warehouse_order_id'] = $orderID;

            $this->WarehouseOrderItem->create();
            $this->WarehouseOrderItem->set($item);

            if ( !$this->WarehouseOrderItem->save() ) {
              $dataSource->rollback();

              $result['validationErrors'] = $this->WarehouseOrderItem->validationErrors;

              return $result;
            }

            // reserve the wanted quantity
            $warehouseLocationItem = $WarehouseLocationItem->find('first',[
              'conditions' => [
                'item_id' => $item['WarehouseOrderItem']['item_id'],
                'warehouse_location_id' => $item['WarehouseOrder']['transfer_from']
              ]
            ]);

            $WarehouseLocationItem->id = $warehouseLocationItem['WarehouseLocationItem']['id'];

            $WarehouseLocationItem->saveField(
              'quantity_available',
              $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] - $item['WarehouseOrderItem']['quantity_wanted']
            );

            $WarehouseLocationItem->saveField(
              'quantity_reserved',
              $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'] + $item['WarehouseOrderItem']['quantity_wanted']
            );
          }
        }

        $dataSource->commit();

        $result['error'] = false;

        return $result;
      } catch(Exception $e) {
        $dataSource->rollback();

        return $result;
      }
    }

    /**
     * Accept an pending order
     * @param $id int
     * @param $data mixed
     * @return ['error' => boolean, 'message' => string]
     */
    public function acceptOrder($id,$data) {
      $result = ['error' => true, 'message' => ''];
      $dataSource = $this->getDataSource();
      // ToDo : find model in associations, dont load it
      $WarehousePermission = ClassRegistry::init('WarehousePermission');
      $WarehouseLocationItem = ClassRegistry::init('WarehouseLocationItem');

      $dataSource->begin();

      try {
        // first check if the user has the permission to accept this order
        $order = $this->find('first',[
          'recursive' => -1,
          'conditions' => ['id' => $id]
        ]);

        if ( !$order ) {
          $result['message'] = 'Prenosnica nije nadjena';

          return $result;
        }

        $warehousePermission = $WarehousePermission->find('first',[
          'conditions' => [
            'user_id' => $data['User']['id'],
            'warehouse_location_id' => $order['WarehouseOrder']['transfer_from']
          ]
        ]);

        if ( !$warehousePermission ) {
          $result['message'] = 'Nemate dozvolu da prihvatite ovu prenosnicu';

          return $result;
        }

        // try to update every order item with quantity issued
        // make sure quantity avaible is greater or equals to the quantity issued
        foreach ( $data['WarehouseOrderItem'] as $warehouseOrderItem ) {
          $item = $this->WarehouseOrderItem->Item->find('first',[
            'recursive' => -1,
            'conditions' => ['id' => $warehouseOrderItem['item_id']]
          ]);

          if ( !$item ) {
            $result['message'] = 'Doslo je do greske, artikal nije nadjen';

            return $result;
          }

          if ( !$warehouseOrderItem['quantity_issued'] ) {
            $result['message'] = 'Niste uneli kolicinu za proizvod ' . $item['Item']['name'];

            return $result;
          }

          // check if it's a number
          if ( !is_numeric($warehouseOrderItem['quantity_issued']) ) {
            $result['message'] = 'Niste uneli kolicinu za proizvod ' . $item['Item']['name'];

            return $result;
          }
          
          if ( $warehouseOrderItem['quantity_issued'] < 1 ) {
            $result['message'] = "Kolicina za proizvod {$item['Item']['name']} mora biti veca od nule";

            return $result;
          }

          // find the item in the warehouse
          $warehouseLocationItem = $WarehouseLocationItem->find('first',[
            'recursive' => -1,
            'conditions' => [
              'WarehouseLocationItem.item_id' => $warehouseOrderItem['item_id'],
              'WarehouseLocationItem.warehouse_location_id' => $order['WarehouseOrder']['transfer_from']
            ]
          ]);

          if ( !$warehouseLocationItem ) {
            $result['message'] = 'Doslo je do greske, artikal nije pronadjen';

            return $result;
          }

          // sum avaible and reserved quantity
          $avaibleSum = $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] +  $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'];

          if ( $avaibleSum < $warehouseOrderItem['quantity_issued'] ) {
            $quantityAvaible = $warehouseLocationItem['WarehouseLocationItem']['quantity_available'];

            $result['message'] = "Raspoloziva kolicina za proizvod {$item['Item']['name']} je {$quantityAvaible}";

            return $result;
          }
          
          // ok now update the quantity issued field
          $orderItem = $this->WarehouseOrderItem->find('first',[
            'recursive' => -1,
            'conditions' => [
              'item_id' => $warehouseOrderItem['item_id'],
              'warehouse_order_id' => $id
            ]
          ]);

          $this->WarehouseOrderItem->id = $orderItem['WarehouseOrderItem']['id'];
          $this->WarehouseOrderItem->saveField('quantity_issued',$warehouseOrderItem['quantity_issued']);
        }

        // change the order status to spreman, and issued by field
        $this->id = $id;

        $this->set($order['WarehouseOrder']['id']);
        $this->set('status','spreman');
        $this->set('issued_by',$data['User']['id']);

        $this->save();

        $dataSource->commit();

        $result['error'] = false;

        return $result;
      } catch(Exception $e) {
        $dataSource->rollback();

        $result['message'] = 'Doslo je do greske, pokusajte ponovo';
        
        return $result;
      }
    }
    
    /**
     * Move the items from the order into the warehouse location
     * @param $id int
     * @param $userID int
     * @return ['error' => boolean, 'message' => string]
     */
    public function completeOrder($id,$userID) {
      $result = ['error' => true, 'message' => ''];
      $dataSource = $this->getDataSource();
      // ToDo : find model in associations, dont load it
      $WarehousePermission = ClassRegistry::init('WarehousePermission');
      $WarehouseLocationItem = ClassRegistry::init('WarehouseLocationItem');
      $WarehouseLocationAddress = ClassRegistry::init('WarehouseLocationAddress');

      try {
        $dataSource->begin();

        $order = $this->find('first',[
          'conditions' => [
            'WarehouseOrder.id' => $id
          ],
          'contain' => [
            'WarehouseOrderItem'
          ]
        ]);

        // check if the user has the permission to complete the order
        $warehousePermission = $WarehousePermission->find('first',[
          'conditions' => [
            'user_id' => $userID,
            'warehouse_location_id' => $order['WarehouseOrder']['transfer_to']
          ]
        ]);

        if ( !$warehousePermission ) {
          $result['message'] = 'Nemate dozvolu da prihvatite ovu prenosnicu';

          return $result;
        }

        if ( $order['WarehouseOrder']['status'] === 'isporucen' ) {
          $result['message'] = 'Roba u prenosnici je vec primljena';

          return $result;
        }

        if ( $order['WarehouseOrder']['status'] !== 'spreman' ) {
          $result['message'] = 'Prenosnica nije odobrena od strane operatera predajnog magacina';

          return $result;
        }

        // for each item, if item doesnt exist in the location, create it
        // otherwise update it
        foreach ( $order['WarehouseOrderItem'] as $warehouseOrderItem ) {
          // $WarehouseLocationAddress->create();

          // check if the article exist at this address
          $address = $WarehouseLocationAddress->find('first',[
            'conditions' => [
              'item_id' => $warehouseOrderItem['item_id'],
              'warehouse_address_id' => $warehouseOrderItem['warehouse_address_received_id']
            ]
          ]);

          // item is found 
          if ( $address ) {
            $WarehouseLocationAddress->id = $address['WarehouseLocationAddress']['id'];

            $isSaved = $WarehouseLocationAddress->saveField(
              'quantity',
              $address['WarehouseLocationAddress']['quantity'] + $warehouseOrderItem['quantity_issued']
            );

            if ( !$isSaved ) {
              $dataSource->rollback();

              $result['message'] = 'Doslo je do greske, pokusajte ponovo';

              return $result;
            }

          } else {
            $WarehouseLocationAddress->create();

            $isSaved = $WarehouseLocationAddress->save([
              'WarehouseLocationAddress' => [
                'item_id' => $warehouseOrderItem['item_id'],
                'warehouse_address_id' => $warehouseOrderItem['warehouse_address_received_id'],
                'quantity' => $warehouseOrderItem['quantity_issued']
              ]
            ]);

            if ( !$isSaved ) {
              $dataSource->rollback();

              $result['message'] = 'Doslo je do greske, pokusajte ponovo';

              return $result;
            }
          }

          $warehouseLocationItem = $WarehouseLocationItem->find('first',[
            'conditions' => [
              'item_id' => $warehouseOrderItem['item_id'],
              'warehouse_location_id' => $order['WarehouseOrder']['transfer_from']
            ]
          ]);

          $WarehouseLocationItem->id = $warehouseLocationItem['WarehouseLocationItem']['id'];

          // add this to the avaible quantity
          $difference = $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'] - $warehouseOrderItem['quantity_issued'];
          $quantityAvaible = $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] + $difference;

          $reservedQuantity = $warehouseOrderItem['quantity_issued'] > $warehouseOrderItem['quantity_wanted']
            ? $warehouseOrderItem['quantity_issued']
            : $warehouseOrderItem['quantity_wanted'];

          $WarehouseLocationItem->set([
            'WarehouseLocationItem' => [
              'id' => $warehouseLocationItem['WarehouseLocationItem']['id'],
              'quantity_reserved' => $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'] - $reservedQuantity,
              'quantity_available' => $quantityAvaible
            ]
          ]);

          if ( !$WarehouseLocationItem->save() ) {
            $dataSource->rollback();

            $result['message'] = 'Doslo je do greske, pokusajte ponovo';
    
            return $result;
          }

          // now create or update location items table with the new items
          // if found just update the quanity
          // if not make a new record
          $warehouseLocationItem = $WarehouseLocationItem->find('first',[
            'conditions' => [
              'item_id' => $warehouseOrderItem['item_id'],
              'warehouse_location_id' => $order['WarehouseOrder']['transfer_to']
            ]
          ]);

          if ( $warehouseLocationItem ) {
            $WarehouseLocationItem->id = $warehouseLocationItem['WarehouseLocationItem']['id'];

            $isSaved = $WarehouseLocationAddress->saveField(
              'quantity_available',
              $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] + $warehouseOrderItem['quantity_issued']
            );

            if ( !$isSaved ) {
              $dataSource->rollback();

              $result['message'] = 'Doslo je do greske, pokusajte ponovo';
      
              return $result;
            }
          } else {
            $WarehouseLocationItem->create();

            $WarehouseLocationItem->set([
              'WarehouseLocationItem' => [
                'warehouse_location_id' => $order['WarehouseOrder']['transfer_to'],
                'item_id' => $warehouseOrderItem['item_id'],
                'quantity_available' => $warehouseOrderItem['quantity_issued']
              ]
            ]);

            if ( !$WarehouseLocationItem->save() ) {
              $dataSource->rollback();

              $result['message'] = 'Doslo je do greske, pokusajte ponovo';
      
              return $result;
            }
          }
        }

        //update the status of the order 
        $this->id = $id;

        $this->set([
          'WarehouseOrder' => [
            'status' => 'isporucen',
            'received_by' => $userID
          ]
        ]);

        if ( !$this->save() ) {
          $dataSource->rollback();

          $result['message'] = 'Doslo je do greske, pokusajte ponovo';

          return $result;
        }

        $dataSource->commit();

        $result['error'] = false;

        return $result;

      } catch(Exception $e) {
        $dataSource->rollback();

        $result['message'] = 'Doslo je do greske, pokusajte ponovo';

        return $result;
      }
    }

    /**
     * Cancel the order
     * @param $id int
     * @return ['error' => boolean, 'message' => string]
     */
    public function cancelOrder($id,$userID) {
      $result = ['error' => true,'message' => ''];
      $dataSource = $this->getDataSource();
      // ToDo : find model in associations, dont load it
      $WarehousePermission = ClassRegistry::init('WarehousePermission');
      $WarehouseLocationItem = ClassRegistry::init('WarehouseLocationItem');

      try {
        $dataSource->begin();

        $order = $this->find('first',[
          'conditions' => ['WarehouseOrder.id' => $id],
          'contain' => 'WarehouseOrderItem'
        ]);

        if ( !$order ) {
          $result['message'] = 'Prenosnica nije nadjena';

          return $result;
        }

        if ( in_array($order['WarehouseOrder']['status'],['otvoren','isporucen','otkazan']) ) {
          $result['message'] = 'Nije moguce otkazati prenosnicu';

          return $result;
        }

        // check if a user has the permission to cancel this order
        // operaters from both warehouses can cancel the order
        $permission = $WarehousePermission->find('first',[
          'conditions' => [
            'user_id' => $userID,
            'warehouse_location_id' => [$order['WarehouseOrder']['transfer_to'], $order['WarehouseOrder']['transfer_from']]
          ]
        ]);

        if ( !$permission ) {
          $result['message'] = 'Nemate dozvolu da otkazete ovu prenosnicu';

          return $result;
        }

        // now return all reserved items
        foreach ( $order['WarehouseOrderItem'] as $orderItem ) {
          $warehouseLocationItem = $WarehouseLocationItem->find('first',[
            'recursive' => -1,
            'conditions' => [
              'item_id' => $orderItem['item_id'],
              'warehouse_location_id' => $order['WarehouseOrder']['transfer_from']
            ]
          ]);

          $WarehouseLocationItem->set([
            'WarehouseLocationItem' => [
              'id' => $warehouseLocationItem['WarehouseLocationItem']['id'],
              'quantity_reserved' => $warehouseLocationItem['WarehouseLocationItem']['quantity_reserved'] - $orderItem['quantity_wanted'],
              'quantity_available' =>  $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] + $orderItem['quantity_wanted']
            ]
          ]);

          if ( !$WarehouseLocationItem->save() ) {
            $result['message'] = 'Doslo je do greske, pokusajte ponovo';

            return $result;
          }
        }

        //update the status of the order 
        $this->id = $id;

        $this->set([
          'WarehouseOrder' => [
            'status' => 'otkazan',
          ]
        ]);

        if ( !$this->save() ) {
          $dataSource->rollback();

          $result['message'] = 'Doslo je do greske, pokusajte ponovo';

          return $result;
        }

        $dataSource->commit();

        $result['error'] = false;

        return $result;

      } catch(Exception $e) {
        $dataSource->rollback();

        $result['message'] = 'Doslo je do greske, pokusajte ponovo';

        return $result;
      }
    }

    public function getFormatedWarehouses() {
      return $this->find('list');
    }

    /**
     * Delete order from database
     * Can be deleted by only the user who created it, and if status is otvoreno
     * @param $orderID int 
     * @param $userID int 
     * @return ['error' => boolean, 'message' => string]
     */
    public function remove($orderID,$userID) {
      $output = ['message' => '', 'error' => true];

      $order = $this->find('first',[
        'recursive' => -1,
        'conditions' => [
          'id' => $orderID
        ]
      ]);

      if ( !$order ) {
        $output['message'] = 'Prenosnica nije nadjena';

        return $output;
      }

      if ( $order['WarehouseOrder']['created_by_id'] != $userID ) {
        $output['message'] = 'Nemate dozvolu da obrisete ovu prenosnicu';

        return $output;
      }

      if ( $order['WarehouseOrder']['status'] != 'otvoren' ) {
        $output['message'] = 'Nije moguce brisati prenosnicu u ovom statusu';

        return $output;
      }

      if ( !$this->delete($orderID) ) {
        $output['message'] = 'Doslo je do greske, pokusajte ponovo';

        return $output;
      }

      $output['error'] = false;

      return $output;
    }
  }