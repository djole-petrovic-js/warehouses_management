<?php
  App::uses('Model', 'Model');
  App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

  class User extends Model {
    public $actsAs = ['Containable','Acl' => ['type' => 'requester', 'enabled' => false]];

    public $belongsTo = [
      'Group' => [
        'type' => 'INNER',
        'foreignKey' => 'group_id'
      ]
    ];
    
    public $hasMany = [
      'WarehouseOrder' => [
        'type' => 'INNER',
        'foreignKey' => 'created_by_id'
      ]
    ];
    
    public $validate = [
      'username' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Korisnicko ime mora biti uneto'
        ],
        'isUnique' => [
          'rule' => 'isUnique',
          'message' => 'Korisnicko ime vec postoji'
        ],
        'regex' => [
          'rule' => '/^[a-zA-Z0-9\.]{5,20}$/',
          'message' => 'Korisnicko ime mora imati samo brojeve i slova, od 5 do 20 karaktera'
        ]
      ],
      'password' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Lozinka mora biti uneta'
        ],
        'regex' => [
          'rule' => '/^[a-zA-Z0-9]{5,10}$/',
          'message' => 'Lozinka mora imati samo brojeve i slova, od 5 do 10 karaktera'
        ]
      ],
      'first_name' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Ime mora biti uneto'
        ]
      ],
      'last_name' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Prezime mora biti uneto'
        ]
      ]
    ];

    // for select box in views
    public function getFormatedUsers() {
      return $this->find('list',[
        'fields' => ['id','username']
      ]);
    }

    public function beforeSave($options = array()) {
      if ( isset($this->data[$this->alias]['password']) ) {
        $passwordHasher = new BlowfishPasswordHasher();

        $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
      }

      return true;
    }

    public function remove($id) {
      $output = ['message' => '', 'error' => true];

      $output['message'] = 'Brisanje korisnika trenutno nije moguce';

      return $output;
    }

    public function bindNode($user) {
      return ['model' => 'Group', 'foreign_key' => $user['User']['group_id']];
    } 

    public function parentNode() {
      if ( !$this->id && empty($this->data) ) {
        return null;
      }

      if  (isset($this->data['User']['group_id'] )) {
        $groupId = $this->data['User']['group_id'];
      } else {
        $groupId = $this->field('group_id');
      }

      if (!$groupId) {
        return null;
      }

      return ['Group' => ['id' => $groupId]];
  }
  }