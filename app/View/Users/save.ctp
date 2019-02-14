<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj korisnika.' : 'Kreiraj novog korisnika'; ?></h3>
  <?php echo $this->Form->create('User'); ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td><?= __('Korisnicko Ime') ?></td>
          <td>
            <?= $this->Form->input('username',['label' => false]) ?>
          </td>
        </tr>
        <?php if ( !isset($updateRequest) ): ?>
          <tr>
            <td><?= __('Lozinka') ?></td>
            <td>
              <?= $this->Form->input('password',['label' => false,'type' => 'password']) ?>
            </td>
          </tr>
        <?php endif ?>
        <tr>
          <td><?= __('Ime') ?></td>
          <td>
            <?= $this->Form->input('first_name',['label' => false]) ?>
          </td>
        </tr>
        <tr>
          <td><?= __('Prezime') ?></td>
          <td>
            <?= $this->Form->input('last_name',['label' => false]) ?>
          </td>
        </tr>
        <tr>
          <td><?= __('Grupa'); ?></td>
          <td><?= $this->Form->input('group_id',[
            'type' => 'select',
            'options' => $groups,
            'label' => false
          ]) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>