<?php
  App::uses('Model', 'Model');

  class Product extends Model {
    public $actsAs = ['Containable'];
    
    private $statuses = [
      'development' => 'development',
      'for sale' => 'for sale',
      'phase out' => 'phase out',
      'obsolete' => 'obsolete',
      'nrnd' => 'nrnd'
    ];

    public $belongsTo = [
      'Item' => [
        'foreignKey' => 'id'
      ]
    ];

    public $validate = [
      'pid' => [
        'validatePID' => [
          'rule' => ['combinedValidation','pid'],
          'message' => 'PID je obavezan kad je status: for sale, phase out ili obsolete'
        ],
        'unique' => [
          'rule' => 'isUnique',
          'message' => 'Vec postoji proizvod sa ovim PID-om'
        ]
      ],
      'hts_number' => [
        'validateHSNumber' => [
          'rule' => ['combinedValidation','hts_number'],
          'message' => 'HS Number je obavezan kad je status: for sale, phase out ili obsolete'
        ]
      ],
      'tax_group' => [
        'validateTaxGroup' => [
          'rule' => ['combinedValidation','tax_group'],
          'message' => 'Tax group je obavezan kad je status: for sale, phase out ili obsolete'
        ]
      ],
      'product_eccn' => [
        'validateEccn' => [
          'rule' => ['combinedValidation','product_eccn'],
        ]
      ]
    ];

    // set release date if setting status from development to sale
    public function beforeValidate($options = []) {
      if($this->Item->data['Item']['status'] === 'for sale' && empty($this->data['Product']['product_release_date'])){
        $this->data['Product']['product_release_date'] = date('Y-m-d');
      }
      return true;
    }

    public function getStatuses() {
      return $this->statuses;
    }

    // validation for pid, hs number, tax group, and obsolete
    public function combinedValidation($value,$key) {
      // if status is not for sale, phase out or obsolete, just skip validation
      if ( !in_array($this->Item->data['Item']['status'],['for sale','phase out','obsolete']) ) {
        return true;
      }

      if ( empty($value[$key]) ) {
        return "{$key} je obavezan kad je status: for sale, phase out ili obsolete";
      }

      if ( $key === 'product_eccn' ) {
        if ( !preg_match('/^[a-zA-Z0-9]{5}$/',$value[$key]) ) {
          return 'ECCN mora biti pravilno formatiran';
        }
      }

      return true;
    }
  }