<?php
  App::uses('Model', 'Model');

  class AroModel extends Model {
    public $actsAs = ['Containable'];
    public $useTable = 'aros';

    public $belongsTo = [
      'Group' => [
        'type' => 'INNER',
        'foreignKey' => 'foreign_key'
      ]
    ];
  }