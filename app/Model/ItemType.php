<?php
  App::uses('Model', 'Model');

  class ItemType extends Model {
    public $actsAs = ['Containable'];

    public $hasMany = [
      'Item' => [
        'type' => 'INNER',
        'foreignKey' => 'item_type_id',
      ]
    ];

    //key values pairs id => name for select boxex
    public function getFormatedData($conditions) {
      return $this->find('list');
    }
  }