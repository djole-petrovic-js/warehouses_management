<?php $this->start('main_placeholder'); ?>
  <h3>Login</h3>
  <?php echo $this->Form->create('User'); ?>
    <?php echo $this->Flash->render(); ?>
    <div>
      <p><?= __('Korisnicko Ime') ?></p>
      <p>
        <?= $this->Form->input('username',['label' => false]) ?>
      </p>
    </div>
    <div>
      <p><?= __('Lozinka') ?></p>
      <p>
      <?= $this->Form->input('password',['label' => false,'type' => 'password']) ?>
      </p>
    </div>
  <?php echo $this->Form->end('Login') ?>
<?php $this->end(); ?>