<?php
  App::uses('Model', 'Model');

  class WarehouseAddress extends Model {
    public $actsAs = ['Containable'];
    
    public $belongsTo = [
      'WarehouseLocation' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ]
    ];

    public $hasMany = [
      'WarehouseLocationAddress' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_address_id'
      ]
    ];

    public $validate = [
      'row' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Red mora biti unet'
        ],
      ],
      'shelf' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Broj police mora biti unet'
        ],
      ],
      'box' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Broj pregrade mora biti unet'
        ],
      ],
      'warehouse_location_id' => [
        'checkIfAddressExists' => [
          'rule' => 'checkIfAddressExists',
          'message' => 'Adresa na ovom magacinskom mestu vec postoji'
        ]
      ]
    ];

    public function checkIfAddressExists() {
      $warehouseAddress = $this->find('first',[
        'recursive' => -1,
        'conditions' => [
          'AND' => [
            'row' => $this->data['WarehouseAddress']['row'],
            'shelf' => $this->data['WarehouseAddress']['shelf'],
            'box' => $this->data['WarehouseAddress']['box'],
            'warehouse_location_id' => $this->data['WarehouseAddress']['warehouse_location_id']
          ]
        ]
      ]);

      if ( $warehouseAddress ) return false;

      return true;
    }
    
    // makes complete barcode
    private function _makeBarcode(...$fragments) {
      $barcode = '';
      // first concat all fragments together
      // checksum digit will be calculated later
      foreach ( $fragments as $fragment ) {
        $barcode .= $fragment;
      }

      // start calculating checksum digit
      // first break the barcode into character array
      $barcodeArray = str_split($barcode);
      // multiply each number, numbers at odd indexes are multiplied by 3, odd are just added to the sum
      $multiplicationFragments = [];

      for ( $i = 0 ; $i < count($barcodeArray) ; $i++ ) {
        $multiplicationFragments[] =  $i % 2 === 0 ? $barcodeArray[$i] : $barcodeArray[$i] * 3;
      }

      // add all fragments together
      $sum = array_reduce($multiplicationFragments,function($acc,$item){
        $acc += $item; return $acc;
      },0);

      // find nearest equal or higher multiple of ten
      $nearest10Multiple = $sum % 10 === 0 ? $sum : $sum + (10 - $sum % 10);
      // Subtract the sum from nearest 10 multiple
      $checkSumDigit = $nearest10Multiple - $sum;

      return $barcode . $checkSumDigit;
    }

    public function beforeSave($options = []) {
      //set code
      $row = $this->data['WarehouseAddress']['row'];
      $shelf = $this->data['WarehouseAddress']['shelf'];
      $box = $this->data['WarehouseAddress']['box'];
      $warehouseLocationID = $this->data['WarehouseAddress']['warehouse_location_id'];

      $warehouseLocation = $this->WarehouseLocation->find('first',[
        'conditions' => ['id' => $warehouseLocationID],
        'recursive' => -1
      ]);

      $this->data['WarehouseAddress']['code'] = $warehouseLocation['WarehouseLocation']['code'] . '_' . $row . '_' . $shelf . '_' . $box;
      // set barcode
      // DD = double digit
      $barcodePrefix = '2912';
      $DDlocationID = $warehouseLocationID > 10 ? $warehouseLocationID : '0' . $warehouseLocationID;
      $rowASCIICode = ord($row);
      $DDshelf = $shelf > 10 ? $shelf : '0' . $shelf;
      $DDbox = $box > 10 ? $box : '0' . $box;

      $this->data['WarehouseAddress']['barcode'] = $this->_makeBarcode(
        $barcodePrefix,
        $DDlocationID,
        $rowASCIICode,
        $DDshelf,
        $DDbox
      );

      return true;
    }

    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      // find if any product has this address assigned
      $address = $this->WarehouseLocationAddress->find('first',[
        'conditions' => ['warehouse_address_id' => $id]
      ]);

      if ( $address || count($address) > 0 ) {
        $output['message'] = 'Nije moguce obrisati ovo magacinsko mesto jer vec ima dodeljenje proizvode';

        return $output;
      }

      if ( !$this->delete($id) ) {
        $output['message'] = 'Doslo je do greske, probajte ponovo';

        return $output;
      }

      $output['error'] = false;

      return $output;
    }
  }