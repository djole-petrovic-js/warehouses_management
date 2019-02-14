<?php
 
  App::uses('Model', 'Model');

  class WarehouseLocationAddress extends Model {
    public $actsAs = ['Containable'];

    public $useTable = 'warehouse_locations_addresses';

    public $validate = [
      'item_id' => [
        'validateItemID' => [
          'rule' => 'validateItemID',
        ]
      ],
      'warehouse_address_id' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Niste izabrali magacinsku adresu'
        ]
      ],
      'quantity' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Niste izabrali kolicinu'
        ],
        'validateQuantity' => [
          'rule' => 'validateQuantity'
        ]
      ]
    ];

    public $belongsTo = [
      'Item' => [
        'type' => 'INNER',
        'foreignKey' => 'item_id'
      ],
      'WarehouseAddress' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_address_id'
      ]
    ];

    public function validateQuantity($value) {
      if ( !is_numeric($value['quantity']) ) {
        return 'Morate uneti broj';
      }

      if ( $value['quantity'] < 0 ) {
        return 'Neispravan unos, broj mora biti veci od nule';
      }

      return true;
    }

    public function saveMultipleRecords($data) {
      $dataSource = $this->getDataSource();

      try {
        $dataSource->begin();

        foreach ( $data['WarehouseLocationAddress']['item_id'] as $itemID ) {
          $this->create();

          $isSaved = $this->save([
            'WarehouseLocationAddress' => [
              'item_id' => $itemID,
              'warehouse_address_id' => $data['WarehouseLocationAddress']['warehouse_address_id']
            ]
          ]);

          if ( !$isSaved ) {
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

    public function validateItemID($value) {
      $item = $this->Item->find('first',[
        'conditions' => ['Item.id' => $value['item_id']],
        'contain' => ['ItemType']
      ]);

      if ( !$item ) {
        return 'Proizvod ne postoji';
      }

      $warehouseLocationAddress = $this->find('first',[
        'recursive' => -1,
        'conditions' => [
          'AND' => [
            'warehouse_address_id' => $this->data['WarehouseLocationAddress']['warehouse_address_id'],
            'item_id' => $value['item_id']
          ]
        ]
      ]);

      if ( $warehouseLocationAddress || count($warehouseLocationAddress) > 0 ) {
        return "Artikal {$item['Item']['name']} se vec nalazi na ovoj adresi";
      }

      if ( !$item['ItemType']['tangible'] ) {
        return 'Artikal nije opipljiv';
      }

      $id = $this->data['WarehouseLocationAddress']['warehouse_address_id'];

      $warehouseAddress = $this->WarehouseAddress->find('first',[
        'conditions' => ['WarehouseAddress.id' => $id ],
        'contain' => [
          'WarehouseLocation' => [
            'WarehouseLocationType'
          ]
        ]
      ]);

      // check if product type that is supplied can go into this location
      $isItemTypeValid = false;

      foreach ( $warehouseAddress['WarehouseLocation']['WarehouseLocationType'] as $locationType ) {
        if ( $locationType['type'] === $item['ItemType']['type_class'] ) {
          $isItemTypeValid = true;
        }
      }

      if ( !$isItemTypeValid ) {
        return 'Ovaj proizvod se ne moze dodeliti ovom magacinskom mestu';
      }

      return true;
    }

    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      $warehouseLocationAddress = $this->findById($id);

      if ( !$warehouseLocationAddress || count($warehouseLocationAddress) === 0 ) {
        $output['message'] = 'Proizvod na ovo lokaciji nije nadjen';

        return $output;
      }

      if ( !$this->delete($id) ) {
        $output['message'] = 'Doslo je do greske, pokusajte ponovo';

        return $output;
      }

      $output['error'] = false;

      return $output;
    }
  }