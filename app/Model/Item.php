<?php
  App::uses('Model', 'Model');

  class Item extends Model {
    public $actsAs = ['Containable'];

    public $hasOne = [
      'Material' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'SemiProduct' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'Product' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'Goods' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'Kit' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'Consumable' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'Inventory' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'ServiceSupplier' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ],
      'ServiceProduct' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ]
    ];

    public $hasMany = [
      'WarehouseLocationAddress' => [
        'type' => 'INNER',
        'foreignKey' => 'item_id'
      ],
      'WarehouseLocationItem' => [
        'type' => 'INNER',
        'foreignKey' => 'item_id'
      ]
    ];

    public $belongsTo = [
      'ItemType' => [
        'type' => 'INNER',
        'className' => 'ItemType',
      ],
      'MeasurementUnit' => [
        'type' => 'INNER'
      ]
    ];

    private $statuses = [
      'development' => 'development',
      'in use' => 'in use',
      'phase out' => 'phase out',
      'obsolete' => 'obsolete'
    ];

    public $validate = [
      'code' => [
        'not_empty' => [
          'rule' => 'notBlank',
          'message' => 'Sifra proizvoda je prazna'
        ],
        'unique' => [
          'rule' => 'isUnique',
          'message' => 'Vec postoji repromaterijal sa ovom sifrom'
        ]
      ],
      'weight' => [
        'validateWeight' => [
          'rule' => 'validateWeight'
        ]
      ],
      'name' => [
        'not_empty' => [
          'rule' => 'notBlank',
          'message' => 'Ime ne sme biti prazno'
        ],
        'unique' => [
          'rule' => 'isUnique',
          'message' => 'Ime vec postoji u sistemu'
        ]
      ],
      'measurement_unit_id' => [
        'validateMeasurementUnitId' => [
          'rule' => 'validateMeasurementUnitId'
        ]
      ],
      'item_type_id' => [
        'validateItemType' => [
          'rule' => 'validateItemType',
        ]
      ],
      'status' => [
        'validateStatus' => [
          'rule' => 'validateStatus'
        ]
      ]
    ];

    // if ID is set for updating, dont generate new code
    public function beforeSave($options = array()) {
      $ItemType = ClassRegistry::init('ItemType');

      $id = $this->data['Item']['id'] ?? null;

      if ( $id ) return;

      $itemType = $ItemType->findById($this->data['Item']['item_type_id']);

      $count = $this->find('count',[
        'recursive' => -1,
        'conditions' => ['item_type_id' => $this->data['Item']['item_type_id']]
      ]);

      $item = $this->findById($this->data['Item']['item_type_id']);

      $this->data['Item']['code'] = $itemType['ItemType']['code'] . '-' . ($count + 1);

      return true;
    }

    public function getStatuses() {
      return $this->statuses;
    }

    public function setStatuses($statuses) {
      $this->statuses = $statuses;
    }

    public function validateWeight($value) {
      if ( empty($value['weight']) ) return true;

      $itemType = $this->ItemType->findById($this->data['Item']['item_type_id']);

      // check if tangible
      if ( !$itemType['ItemType']['tangible'] ) {
        return 'Ne mozete dodeliti tezinu.';
      }

      // check if value is a number
      return  is_numeric($value['weight']) ? true : 'Tezina nije broj, ponovite unos';
    }

    public function validateItemType($value) {
      if ( empty($value['item_type_id']) ) {
        return 'Niste izabrali tip repromaterijala';
      }

      $itemType = $this->ItemType->findById($value['item_type_id']);

      if ( !$itemType ) {
        return 'Niste izabrali tip repromaterijala';
      }

      return true;
    }

    public function validateMeasurementUnitId($value) {
      if ( empty($value['measurement_unit_id']) ) {
        return 'Niste izabrali jedinicu mere';
      }

      $MeasurementUnit = ClassRegistry::init('MeasurementUnit');
      $measurementUnit = $MeasurementUnit->findById($value['measurement_unit_id']);

      if ( !$measurementUnit ) {
        return 'Niste izabrali jedinicu mere';
      }

      if ( !$measurementUnit['MeasurementUnit']['active'] ) {
        return 'Jedinica mere nije aktivna';
      }

      return true;
    }

    public function validateStatus($value) {
      if ( empty($value['status']) ) return 'Niste izabrali status';

      foreach ( $this->statuses as $id => $label ) {
        if ( $id === $value['status'] ) return true;
      }

      return 'Niste izabrali status';
    }

    // ToDo
    // make sure item is not used elsewhere...
    public function remove($id) {
      $result = ['message' => '', 'error' => true];

      if ( !$id ) {
        $result['message'] = 'Neispravan zahtev';

        return $result;
      }

      $item = $this->find('first',[
        'recursive' => -1,
        'conditions' => [
          'id' => $id
        ]
      ]);

      if ( !$item ) {
        $result['message'] = 'Proizvod nije nadjen';

        return $result;
      }

      if ( !$this->delete($id) ) {
        $result['message'] = 'Doslo je do greske pri brisanju.';

        return $result;
      }

      $result['error'] = false;

      return $result;
    }
  }