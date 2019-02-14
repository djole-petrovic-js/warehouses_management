<?php

  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class GroupsController extends AppController {
    public $layout = 'main';

    public $uses = ['Group','AroAcoModel'];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $conditions = ['AND' => []];
      $searchQuery = $this->request->query('search');

      $this->request->data['Item'] = $this->request->query;
  
      if ( $searchQuery ) {
        $conditions['AND']['name LIKE'] = "%$searchQuery%";
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
      ];

      $this->set([
        'groups' =>  $this->paginate('Group'),
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->Group->id = $id;
        $this->set('showInactiveCheckbox',true);
        $this->set('updateRequest',true);

        $group = $this->Group->find('first',[
          'conditions' => ['id' => $id]
        ]);

        if ( !$group ) {
          $this->Flash->set('Grupa ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $group;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->Group->create();
        } else {
          $this->request->data['Group']['id'] = $id;
        }

        $this->Group->set($this->request->data);

        if ( $this->Group->save() ) {
          $this->Flash->set('Grupa je uspesno sacuvana',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result =  $this->Group->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Grupa je uspesno obrisana',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }