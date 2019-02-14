<?php

  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class UsersController extends AppController {
    public $layout = 'main';

    public $uses = ['User','Group','AroAcoModel'];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function initDB() {
      $group = $this->User->Group;
      
      // DONE
      // Allow admins to everything
      $group->id = 5;
      $this->Acl->allow($group, 'controllers/AccessControl');
  
      // allow managers to posts and widgets
      // $group->id = 6;
      // $this->Acl->deny($group, 'controllers');
      // $this->Acl->allow($group, 'controllers/WarehouseOrders');
      // $this->Acl->allow($group, 'controllers/users/login');
      
      // DONE
      // allow basic users to log out
      // $this->Acl->allow($group, 'controllers/users/logout');
      // $this->Acl->allow($group, 'controllers/users/login');
  
      echo "all done";
      exit;
    }

    public function beforeFilter() {
      parent::beforeFilter();

      $this->Auth->allow(['login','logout']);
    }

    public function _setInitialViewData() {
      $this->set([
        'groups' => $this->Group->getFormatedGroups()
      ]);
    }

    public function index() {
      $conditions = ['AND' => []];
      $searchQuery = $this->request->query('search');

      $this->request->data['Item'] = $this->request->query;
  
      if ( $searchQuery ) {
        $conditions['AND']['username LIKE'] = "%$searchQuery%";
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'contain' => ['Group']
      ];

      $this->set([
        'users' =>  $this->paginate('User'),
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->User->id = $id;
        $this->set('showInactiveCheckbox',true);
        $this->set('updateRequest',true);

        $user = $this->User->find('first',[
          'conditions' => [
            'User.id' => $id
          ],
          'contain' => [
            'Group'
          ]
        ]);

        if ( !$user ) {
          $this->Flash->set('Potrosni materijal ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $user;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->User->create();
        } else {
          $this->request->data['User']['id'] = $id;
        }

        $this->User->set($this->request->data);

        if ( $this->User->save() ) {
          $this->Flash->set('Korisnik je uspesno sacuvan',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }

      $this->_setInitialViewData();
    }

    public function login() {
      if ( $this->request->is('post') ) {
        if ( $this->Auth->login() ) {
          // administrators are redirected to start page ( MeasurementUnit )
          if ( $this->Auth->user()['Group']['id'] == 5 ) {
            return $this->redirect([
              'controller' => 'MeasurementUnits',
              'action' => 'index'
            ]);
          }

          // operators are redirected to warehouse orders page
          return $this->redirect([
            'controller' => 'WarehouseOrders',
            'action' => 'index'
          ]);
        }

        $this->Flash->set('Korisnicko ili lozinka su pogresni');
      }
    }

    public function logout() {
      $this->Auth->logout();

      return $this->redirect(['controller' => 'users', 'action' => 'login']);
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result = $this->User->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Korisnik je uspesno obrisan',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }
  }