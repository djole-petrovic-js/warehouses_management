<?php

  App::uses('Model', 'Model');

  class WarehousePermission extends Model {
    public $actsAs = ['Containable'];
    public $useTable = 'warehouse_locations_users';

    public $belongsTo = [
      'User' => [
        'type' => 'INNER',
        'foreignKey' => 'user_id'
      ],
      'WarehouseLocation' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ]
    ];

    public $validate = [
      'warehouse_location_id' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Morate uneti magacinsko mesto'
        ],
        'checkIfPermissionIsAdded' => [
          'rule' => 'checkIfPermissionIsAdded',
          'message' => 'Dozvola za ovog korisnika za ovo magacinsko mesto je vec dodata'
        ]
      ],
      'user_id' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Morate uneti korisnika'
        ]
      ]
    ];

    public function checkIfPermissionIsAdded($value) {
      // dont add the same permission twice, stop if found
      $permission = $this->find('first',[
        'recursive' => -1,
        'conditions' => [
          'warehouse_location_id' => $value['warehouse_location_id'],
          'user_id' => $this->data['WarehousePermission']['user_id']
        ]
      ]);

      if ( $permission ) return false;

      return true;
    }


    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      // if permission doesnt exists, just return the output
      $permission = $this->findById($id);

      if ( !$permission ) {
        $output['message'] = 'Dozvola za prenos nije nadjena';

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