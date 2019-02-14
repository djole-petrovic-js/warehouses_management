<?php
  App::uses('Model', 'Model');

  class AcoModel extends Model {
    public $actsAs = ['Containable','Tree'];
    public $useTable = 'acos';

    public $belongsTo = [
      'Parent' => [
        'className' => 'acos',
        'foreignKey' => 'parent_id'
      ]
    ];

    public $hasMany = [
      'Children' => [
        'className' => 'acos',
        'foreignKey' => 'parent_id'
      ]
    ];
  }