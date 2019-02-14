<?php

  App::uses('Model', 'Model');

  class WarehouseLocation extends Model {
    public $actsAs = ['Containable'];

    public $validate = [
      'code' => [
        'notblank' => [
          'rule' => 'notBlank',
          'message' => 'Sifra mesta nije generisana'
        ],
        'isUnique' => [
          'rule' => 'isUnique',
          'message' => 'Vec postoji magacinsko mesto sa ovom sifrom'
        ]
      ],
      'name' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Ime magacinskog mesta mora biti uneto'
        ],
        'isUnique' => [
          'rule' => 'isUnique',
          'message' => 'Vec postoji magacinsko mesto sa ovim imenom'
        ]
      ],
    ];

    public $hasMany = [
      'WarehousePermission' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ],
      'WarehouseLocationItem' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ],
      'WarehouseLocationType' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ],
      'WarehouseAddress' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ]
    ];

    public $belongsTo = [
      'Warehouse' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_id'
      ]
    ];

    public $types = [
      'product' => 'Proizvod',
      'goods' => 'Dobra',
      'service-product' => 'Usluga',
      'material' => 'Repromaterijali',
      'semi-product' => 'Poluproizvodi',
      'consumable' => 'Potrosna Roba',
      'inventory' => 'Inventar',
    ];

    public function getTypes() {
      return $this->types;
    }

    // for select box in views
    public function getFormatedWarehouses() {
      return $this->find('list');
    }

    public function getFormatedWarehousesForUser($id) {
      return $this->WarehousePermission->find('list',[
        'fields' => ['WarehouseLocation.id','WarehouseLocation.name'],
        'conditions' => ['AND' => ['user_id' => $id, 'permission' => 1]],
        'contain' => ['WarehouseLocation']
      ]);
    }

    public function addOrEditLocationAndAllTypes($data,$id = null) {
      $dataSource = $this->getDataSource();

      try {
        $dataSource->begin();

        if ( $id !== null ) {
          $this->set('id',$id);
        }

        // first try to save or update the location
        if ( !$this->save($data) ) {
          $dataSource->rollback();

          return false;
        }

        // if it is edit request
        // delete old types, and insert new types
        if ( $id !== null ) {
          if ( !$this->WarehouseLocationType->deleteAll(['warehouse_location_id' => $id],false) ) {
            $dataSource->rollback();
  
            return false;
          }
        }

        // id of the warehouse location
        // if id is set, it has the same value,
        // on create request get the insert ID
        $id = $id ?? $this->getInsertID();
        // for every type a warehouse has, enter its type
        // if nothing is entered, just rollback
        if ( !is_array($data['WarehouseLocationType']['type']) ) {
          $dataSource->rollback();

          return false;
        }

        // insert all types associated with this location
        foreach ( $data['WarehouseLocationType']['type'] as $type ) {
          $isTypeSaved = $this->WarehouseLocationType->save([
            'warehouse_location_id' => $id,
            'type' => $type
          ]);

          if ( !$isTypeSaved ) {
            $dataSource->rollback();

            return false;
          }
        }

        $dataSource->commit();

        return true;
      } catch(Exception $e) {
        $dataSource->rollback();

        return false;
      }
    }

    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      $warehouseLocation = $this->find('first',[
        'conditions' => [
          'id' => $id
        ],
        'contain' => [
          'WarehouseAddress'
        ]
      ]);

      if ( !$warehouseLocation ) {
        $output['message'] = 'Magacinsko mesto nije nadjeno';

        return $output;
      }

      if ( count($warehouseLocation['WarehouseAddress']) > 0 ) {
        $output['message'] = 'Magacinsko mesto ne moze biti obrisano jer ima dodeljene magacinske adrese';

        return $output;
      }

      if ( !$this->delete($id) ) {
        $output['message'] = 'Doslo je do greske, probajte ponovo.';

        return $output;
      }

      $output['error'] = false;

      return $output;
    }
  }