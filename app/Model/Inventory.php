<?php
  App::uses('Model', 'Model');

  class Inventory extends Model {
    public $actsAs = ['Containable'];
    
    private $statuses = [
      'draft' => 'draft',
      'in use' => 'in use',
      'phase out' => 'phase out',
      'obsolete' => 'obsolete',
    ];

    private $ratings = [
      'Platinum' => 'Platinum',
      'Gold' => 'Gold',
      'Silver' => 'Silver'
    ];

    public $belongsTo = [
      'Item' => [
        'foreignKey' => 'id'
      ]
    ];

    public function getStatuses() {
      return $this->statuses;
    }

    public function getRatings() {
      return $this->ratings;
    }
  }