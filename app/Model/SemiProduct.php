<?php
  App::uses('Model', 'Model');

  class SemiProduct extends Model {
    public $actsAs = ['Containable'];

    public $belongsTo = [
      'Item' => [
        'foreignKey' => 'id'
      ]
    ];

    public $validate = [
      'service_production' => [
        'validateServiceProduction' => [
          'rule' => 'validateServiceProduction',
          'message' => 'Usluzna proizvodnja mora biti definisana'
        ]
      ]
    ];

    public function validateServiceProduction($value) {
      if ( empty($value['service_production']) ) return false;

      return $value['service_production'] === '0' || $value['service_production'] === '1';
    }
  }