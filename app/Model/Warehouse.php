<?php

  App::uses('Model', 'Model');

  class Warehouse extends Model {
    public $actsAs = ['Containable'];
    
    public $validate = [
      'name' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Ime magacina je obavezno'
        ]
      ]
    ];

    public $hasMany = [
      'WarehouseLocation' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_id'
      ]
    ];

    // for select box in views
    public function getFormatedWarehouses() {
      return $this->find('list');
    }

    // check if some warehouse locations are assigned to this warehouse
    // if they are, we cant delete this warehous
    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      $warehouse = $this->find('first',[
        'conditions' => [
          'id' => $id
        ],
        'contain' => [
          'WarehouseLocation'
        ]
      ]);

      if ( !$warehouse ) {
        $output['message'] = 'Magacin nije nadjen';

        return $output;
      }

      if ( count($warehouse['WarehouseLocation']) > 0 ) {
        $output['message'] = 'Brisanje magacina nije moguce jer ima dodeljene magacinske lokacije.';

        return $output;
      }

      if ( !$this->delete($id) ) {
        $output['message'] = 'Doslo je do greske prilikom brisanja, probajte ponovo';

        return $output;
      }

      $output['error'] = false;

      return $output;
    }
  }