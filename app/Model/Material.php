<?php
  App::uses('Model', 'Model');

  class Material extends Model {
    public $actsAs = ['Containable'];

    private $ratings = [
      'Platinum' => 'Platinum',
      'Gold' => 'Gold',
      'Silver' => 'Silver'
    ];

    public $belongsTo = [
      'Item' => [
        'className' => 'Item',
        'foreignKey' => 'id'
      ]
    ];

    public function getRatings() {
      return $this->ratings;
    }
  }