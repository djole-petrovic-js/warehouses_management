<?php

  App::uses('Model', 'Model');

  class WarehouseLocationType extends Model {
    public $actsAs = ['Containable'];

    public $types = [
      'product' => 'product',
      'goods' => 'goods',
      'service-product' => 'service-product',
      'material' => 'material',
      'semi-product' => 'semi-product',
      'consumable' => 'consumable',
      'inventory' => 'inventory',
    ];

    public $hasMany = [
      'WarehouseLocation' => [
        'type' => 'INNER',
        'foreignKey' => 'id'
      ]
    ];

    public $validate = [
      'type' => [
        'validateType' => [
          'rule' => 'validateType',
          'message' => 'Tip koji ste uneli ne postoji'
        ]
      ]
    ];

    public function validateType($data) {
      foreach ( $this->types as $key => $value ) {
        if ( $value === $data['type'] ) {
          return true;
        }
      }

      return false;
    }
  }