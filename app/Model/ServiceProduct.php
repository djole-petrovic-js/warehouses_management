<?php
  App::uses('Model', 'Model');

  class ServiceProduct extends Model {
    public $actsAs = ['Containable'];
    
    private $statuses = [
      'development' => 'development',
      'for sale' => 'for sale',
      'phase out' => 'phase out',
      'obsolete' => 'obsolete'
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
      if( $this->Item->data['Item']['status'] === 'for sale' && empty($this->data['ServiceProduct']['product_release_date']) ){
        $this->data['ServiceProduct']['product_release_date'] = date('Y-m-d');
      }
      return true;
    }

    public function getStatuses() {
      return $this->statuses;
    }

    private function _validateECCNAndHTSNumber($value,$key) {
      $itemTypeID = $this->Item->data['Item']['item_type_id'];
      $tangible = false;

      $itemType = $this->Item->ItemType->find('first',[
        'conditions' => ['id' => $itemTypeID],
        'recursive' => -1
      ]);

      // if item is not tangible and value is present, return an error message
      if ( !$itemType['ItemType']['tangible'] && !empty($value[$key])) {
        return "Usluga nije opipljiva, {$key} polje mora ostati prazno.";
      }

      // if status is in the required list, but the service product is not tangible,
      // validation is passed. Otherwise, for tangible items, go on.
      if ( !in_array($this->Item->data['Item']['status'],['for sale','phase out','obsolete']) ) {
        if ( !$itemType['ItemType']['tangible'] ) {
          return true;
        }
      }

      if ( !$itemType['ItemType']['tangible'] ) return true;

      // item is tangible, and status is in the required list, so validate them.
      if ( $key === 'product_eccn' ) {
        if ( !preg_match('/^[a-zA-Z0-9]{5}$/',$value[$key]) ) {
          return 'ECCN mora biti pravilno formatiran (5 alfanumerickih karaktera)';
        }
      }

      if ( empty($value[$key]) ) return false;

      return true;
    }

    // validation for pid, hs number, tax group, and obsolete
    public function combinedValidation($value,$key) {
      // for hts and eccn, check if service product is tangible
      if ( $key === 'hts_number' || $key === 'product_eccn' ) {
        return $this->_validateECCNAndHTSNumber($value,$key);
      }

      // if status is not for sale, phase out or obsolete, just skip validation
      if ( !in_array($this->Item->data['Item']['status'],['for sale','phase out','obsolete']) ) {
        return true;
      }

      if ( empty($value[$key]) ) {
        return "{$key} je obavezan kad je status: for sale, phase out ili obsolete";
      }

      if ( $key === 'pid' ) {
        // PID is mandatory at this point, so check if unique
        if (
          $this->Item->ServiceProduct->findByPid($value[$key]) ||
          $this->Item->Product->findByPid($value[$key]) ||
          $this->Item->Goods->findByPid($value[$key]) ||
          $this->Item->Kit->findByPid($value[$key])
        ) {
          return 'PID vec postoji.';
        }
      }

      return true;
    }
  }