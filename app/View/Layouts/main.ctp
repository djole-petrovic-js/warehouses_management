<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Cake App</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
  <script src="/js/kickstart.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

  <?= $this->Html->script('jquery_ambiance'); ?>

  <?= $this->Html->css('jquery_ambiance'); ?>
  <link rel="stylesheet" href="/css/kickstart.css" media="all" />
  <link rel="stylesheet" href="/css/styles.css" media="all" />
</head>
<body>
  <div class="container">
    <div class="col_12">
    <ul class="menu">
      <?php $user = AuthComponent::user() ?>
      <?php if ( $user ): ?>
        <?php if ( $user['Group']['id'] == 5 ): ?>
          <?= $this->element('AdminsNavigation'); ?>
        <?php else: ?>
          <?= $this->element('OperatorsNavigation') ?>
        <?php endif; ?>
        <li><?= $this->Html->link('Logout',['controller' => 'users', 'action' => 'logout']) ?></li>
      <?php else: ?>
        <li><?= $this->Html->link('Login',['controller' => 'users', 'action' => 'login']) ?></li>
      <?php endif; ?>
    </ul>
    </div>
    <div class="col_12">
      <?php
          $authError = $this->Session->flash('auth');

          if ( $authError ) {
            echo "<div class='col_12'>
              <div class='notice error'>
                <p>Nemate dozvolu za pristup ovoj lokaciji</p>
              </div>
            </div>";
          }
        ?>
      <?php echo $this->fetch('main_placeholder'); ?>
    </div>
    <div class="clearfix"></div>
  </div>
  <?php echo $this->element('sql_dump'); ?>
</body>
</html>