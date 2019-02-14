<?php
  App::uses('Model', 'Model');

  class Group extends Model {
    public $actsAs = ['Containable','Acl' => ['type' => 'requester']];
    
    public $validate = [
      'name' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Ime grupe je obavezno'
        ]
      ]
    ];

    public $hasMany = [
      'User' => [
        'type' => 'INNER',
        'foreignKey' => 'group_id'
      ]
    ];

    // for select box in views
    public function getFormatedGroups() {
      return array_reduce($this->find('all',['recursive' => -1]),function($acc,$item){
        $acc[$item['Group']['id']] = $item['Group']['name'];

        return $acc;
      },[]);
    }

    public function parentNode() {
      return null;
    }

    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      $group = $this->findById($id);

      if ( !$group ) {
        $output['message'] = 'Grupa nije nadjena';

        return $output;
      }

      // if a user belongs to this group, group cant be deleted
      $user = $this->User->findByGroupId($id);

      if ( $user ) {
        $output['message'] = 'Brisanje nije moguce jer postoji korisnik koji je vezan sa ovom grupom';

        return $output;
      }

      if ( !$this->delete($id) ) {
        $output['message'] = 'Doslo je do greske u brisanju, pokusajte ponovo';

        return $output;
      }


      $output['error'] = false;

      return $output;
    }
  }