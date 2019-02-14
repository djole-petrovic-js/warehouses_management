<?php
  App::uses('Controller', 'Controller');
  App::uses('AppController', 'Controller');

  class AccessControlController extends AppController {
    public $layout = 'main';

    public $uses = [
      'AroAcoModel',
      'AroModel',
      'AcoModel',
      'Group'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      $arosAcos = $this->AroAcoModel->find('all',[
        'contain' => [
          'AroModel' => ['Group'],'AcoModel' => ['Parent']
        ],
        'conditions' => [
          'AND' => [
            'AcoModel.alias !=' => 'controllers',
            'AroAcoModel.aro_id !=' => 1,
            'AroAcoModel._create != -1'
          ]
        ]
      ]);

      $this->set('arosAcos',$arosAcos);
    }

    private function _setViewData() {
      $acos = $this->AcoModel->find('all',[
        'conditions' => ['AcoModel.parent_id' => '1']
      ]);

      $groups = $this->Group->find('all',[
        'conditions' => ['Group.id !=' => 5]
      ]);

      $this->set('acos',$acos);
      $this->set('groups',$groups);
    }
 
    public function save() {
      if ( $this->request->is('post') ) {
        $acoID = $this->request->data['AroAcoModel']['aco_parent_id'];
        $acoChildID = $this->request->data['AroAcoModel']['aco_child_id'];

        $groupID = $this->request->data['AroAcoModel']['group_id'];

        // 5 is id of the administrators group
        if ( !$acoID || !$groupID || $groupID == '5' ) {
          $this->Flash->set('Niste popunili sve podatke',[
            'key' => 'errorMessage'
          ]);

          $this->_setViewData();

          return $this->render();
        }

        $parentAco = $this->AcoModel->find('first',[
          'recursive' => -1,
          'conditions' => [
            'id' => $acoID
          ]
        ]);

        $childAco = null;

        if ( $acoChildID ) {
          $childAco = $this->AcoModel->find('first',[
            'recursive' => -1,
            'conditions' => [
              'id' => $acoChildID
            ]
          ]);
        }

        $route = 'controllers/' . $parentAco['AcoModel']['alias'];

        if ( $childAco ) {
          $route .= '/' . $childAco['AcoModel']['alias'];
        }

        $group = $this->Group;
        $group->id = $groupID;

        if ( $this->Acl->allow($group, $route) ) {
          $this->Flash->set('Dozvola je uspesno uneta',[
            'key' => 'successMessage'
          ]);

          return $this->redirect(['action' => 'index']);
        }
      }

      $this->_setViewData();
    }

    public function fetch_aco_children() {
      $this->autoRender = false;

      $parent_id = $this->request->query('parent_id');

      $acos = $this->AcoModel->find('all',[
        'conditions' => [
          'AcoModel.parent_id' => $parent_id
        ]
      ]);

      return json_encode(['success' => true, 'data' => $acos]);
    }

    public function delete($groupID,$aroAcoID) {
      if ( $this->request->is('post') ) {
        if ( !$groupID || !$aroAcoID ) {
          $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        $acoAro = $this->AroAcoModel->find('first',[
          'conditions' => ['AroAcoModel.id' => $aroAcoID],
          'contain' => [
            'AroModel','AcoModel' => ['Parent']
          ]
        ]);

        $route = 'controllers/';

        if ( $acoAro['AcoModel']['Parent']['alias'] === 'controllers' ) {
          $route .= $acoAro['AcoModel']['alias'];
        } else {
          $route .= $acoAro['AcoModel']['Parent']['alias'] . '/';
          $route .= $acoAro['AcoModel']['alias'];
        }

        $group = $this->Group;

        $group->id = $groupID;

        if ( $this->Acl->deny($group,$route) ) {
          $this->Flash->set('Dozvola je uspesno oduzeta',['key' => 'successMessage']);
        } else {
          $this->Flash->set('Doslo je do greske, pokusajte ponovo',['key' => 'errorMessage']);
        }

        return $this->redirect(['action' => 'index']);
      }

      $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

      return $this->redirect(['action' => 'index']);
    }
  }