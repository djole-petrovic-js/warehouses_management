<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class MeasurementUnitsController extends AppController {
    public $layout = 'main';

    public $uses = [
      'MeasurementUnit',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      // Set conditions in where clause if search query or show inactive flag are present
      $conditions = ['AND' => []];
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('inactive');

      $this->request->data['MeasurementUnit'] = $this->request->query;
 
      if ( $searchQuery ) {
        $conditions['AND']['name LIKE'] = "%$searchQuery%";
      }

      if ( !$showInactive ) {
        $conditions['AND']['active'] = 1;
      }

      $this->set([
        'measurementUnits' => $this->MeasurementUnit->find('all',['conditions' => $conditions]),
        'searchQuery' => $searchQuery,
        'showInactive' => $showInactive
      ]);
    }

    public function save($id = null) {
      // if id is not null, process edit requests.
      if ( $id != null ) {
        $this->MeasurementUnit->id = $id;
        $this->set('updateRequest',true);

        $measurementUnit = $this->MeasurementUnit->findById($id);

        if ( !$measurementUnit ) {
          $this->Flash->set('Jedinica mere nije nadjena',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        // if edit request, show active/inactive checkbox
        $this->set('isEditRequest',true);

        if ( !$this->request->data ) {
          $this->request->data = $measurementUnit;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        // if post, create new unit.
        if ( $this->request->is('post') ) {
          $this->MeasurementUnit->create();
        }

        if ( $this->MeasurementUnit->save($this->request->data) ) {
          $message = $this->request->is('post') ? 'Uspesno dodato' : 'Uspesno azurirano';

          $this->Flash->set($message,['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }
    }

    public function delete($id = null) {
      // block get requests, or block if $id is not present.
      if ( $this->request->is('get') || !$id) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result = $this->MeasurementUnit->remove($id);

      // measurement unit might not exist, or is connected to a product. 
      if ( !$result['error'] ) {
        $this->Flash->set('Jedinica mere je uspesno obrisana',['key' => 'successMessage']);
      } else {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }