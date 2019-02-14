<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class WarehouseLocationItemsController  extends AppController {
    public $layout = 'main';

    public $uses = [
      'WarehouseLocation',
      'WarehouseLocationItem',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $formatedLocations = $this->WarehouseLocation->getFormatedWarehouses();
      $warehouse_location_id = $this->request->query('warehouse_location_id') ?? array_keys($formatedLocations)[0];
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('inactive');

      $this->request->data['WarehouseLocation'] = $this->request->query;

      $conditions =  ['AND' => ['warehouse_location_id' => $warehouse_location_id]];

      if ( $searchQuery ) {
        $conditions['AND']['name LIKE'] = "%{$searchQuery}%";
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'contain' => [
          'Item'
        ]
      ];

      $this->set([
        'warehouses' => $formatedLocations,
        'warehouseItems' => $this->paginate('WarehouseLocationItem')
      ]);
    }
  }