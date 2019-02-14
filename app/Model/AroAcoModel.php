<?php
  App::uses('Model', 'Model');

  class AroAcoModel extends Model {
    public $actsAs = ['Containable','Tree'];
    public $useTable = 'aros_acos';

    public $belongsTo = [
      'AroModel' => [
        'foreignKey' => 'aro_id'
      ],
      'AcoModel' => [
        'foreignKey' => 'aco_id'
      ]
    ];

    public $validate = [
      'aco_id' => [
        'notBlank' => [
          'rule' => 'notBlank',
          'message' => 'Dozvola je obavezna'
        ]
      ]
    ];

    private $linksAliases = [
      'Inventories' => 'Inventar',
      'Goods' => 'Roba',
      'Consumables' => 'Potrosna Roba',
      'Products' => 'Proizvodi',
      'Kits' => 'Kit',
      'Materials' => 'Repromaterijali',
      'MeasurementUnits' => 'Jedinice Mere',
      'SemiProducts' => 'Poluproizvodi',
      'ServiceSuppliers' => 'Usluge Dobavljaca',
      'ServiceProducts' => 'Usluge',
      'Warehouses' => 'Magacini',
      'WarehouseLocations' => 'Magacinska Mesta',
      'WarehouseAddresses' => 'Magacinske Adrese',
      'WarehouseLocationItems' => 'Sifarnici Magacinskih Mesta',
      'WarehouseLocationAddresses' => 'Adrese Artikala',
      'WarehouseOrders' => 'Prenosnice',
      'Users' => 'Korisnici',
      'Groups' => 'Grupe',
      'WarehousePermissions' => 'Dozvole za prenos',
      'AccessControl' => 'Kontrola Pristupa',
      'MeasurementUnits' => 'Jedinice Mere'
    ];

    public function resolveControllerName($name) {
      return $this->linksAliases[$name];
    }

    private $operatorsNavigationStructure = [
      'Artikli' => [
        'Inventories',
        'Goods',
        'Consumables',
        'Products',
        'Kits',
        'Materials',
        'SemiProducts',
        'ServiceSuppliers',
        'ServiceProducts',
        'MeasurementUnits'
      ],
      'Magacinsko Poslovanje' => [
        'WarehouseOrders',
        'Warehouses',
        'WarehouseLocations',
        'WarehouseAddresses',
        'WarehouseLocationItems',
      ],
      'Upravljanje Nalozima' => [
        'Users',
        'Groups',
        'WarehousePermissions',
        'AccessControl',
      ]
    ];

    public function getNavigationForOperaters() {
      $arosAcos = $this->find('all',[
        'contain' => [
          'AroModel','AcoModel' => ['Parent','Children']
        ],
        'conditions' => [
          'AND' => [
            'AcoModel.alias !=' => 'controllers',
            'AroAcoModel.aro_id' => 2,
            'AroAcoModel._create != -1'
          ]
        ]
      ]);

      $fullNavigation = [
        'Artikli' => [],
        'Magacinsko Poslovanje' => [],
        'Upravljanje Nalozima' => []
      ];

      foreach ( $arosAcos as $aroAco ) {
        $link = ['controller' => '', 'label' => ''];

        if ( count($aroAco['AcoModel']['Children']) === 0 ) {
          $controller = $aroAco['AcoModel']['Parent']['alias'];

          if ( $aroAco['AcoModel']['alias'] === 'index' ) {
            $link = ['label' => $this->linksAliases[$controller] ?? $controller,'controller' => $controller];
          }
        } else {
          $controller = $aroAco['AcoModel']['alias'];
          $link= ['label' => $this->linksAliases[$controller] ?? $controller,'controller' => $controller];
        }

        // try to add link in correct place in navigation tree
        foreach ( array_keys($fullNavigation) as $key ) {
          if ( $this->operatorsNavigationStructure[$key] ) {
            foreach ( $this->operatorsNavigationStructure[$key] as $operatorController ) {
              if ( $operatorController === $controller ) {
                // skip links that are not meant to be show in main navigation
                if ( in_array($aroAco['AcoModel']['alias'],['login']) ) {
                  continue;
                }

                $fullNavigation[$key][] = $link;
              }
            }
          }
        }
      }

      return $fullNavigation;
    }

    public function remove($id) {
      $result = ['error' => true, 'message' => ''];

      $controll = $this->find('first',[
        'recursive' => -1,
        'conditions' => ['AroAcoModel.id' => $id]
      ]);

      if ( !$controll ) {
        $result['message'] = 'Dozvola ne postoji';

        return $result;
      }

      if ( !$this->delete($id) ) {
        $result['message'] = 'Doslo je do greske, pokusajte ponovo';

        return $result;
      }

      $result['error'] = false;

      return $result;
    }
  }