<?php
  App::uses('Model', 'Model');

  class MeasurementUnit extends Model {
    public $hasMany = [
      'Item' => [
        'type' => 'INNER',
        'foreignKey' => 'measurement_unit_id'
      ]
    ];

    public $validate = [
      'name' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Polje naziv ne sme biti prazno'
        ],
        'unique' => [
          'rule' => 'isUnique',
          'message' => 'Polje koje ste uneli se vec postoji'
        ]
      ],
      'symbol' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Polje za simbol ne sme biti prazno'
        ],
        'unique' => [
          'rule' => 'isUnique',
          'message' => 'Simbol koji ste uneli vec postoji'
        ]
      ]
    ];

    public function getFormatedData() {
      return $this->find('list');
    }

    public function remove($id) {
      $output = ['message' => '','error' => true];

      $this->id = $id;

      if ( !$this->exists() ) {
        $output['message'] = 'Jedinica mere ne postoji';

        return $output;
      }

      $Item = ClassRegistry::init('Item');

      if ( $Item->findByMeasurementUnitId($id,['recursive' => false]) ) {
        $output['message'] = 'Brisanje nije moguce jer postoje artikli koji koriste ovu jedinicu mere.';

        return $output;
      }

      if ( !$this->delete($id) ) {
        $output['message'] = 'Doslo je do greske prilikom brisanja';
      }

      $output['error'] = false;

      return $output;
    }
  }

