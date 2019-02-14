<?php foreach ( array_keys($navigation) as $key ): ?>
  <?php if ( count($navigation[$key]) === 0 ) { continue; } ?>
  <li><a href="#"><?= $key ?></a>

  <ul>
    <?php foreach ( $navigation[$key] as $link ): ?>
      <li><?= $this->Html->link($link['label'],['controller' => $link['controller'], 'action' => 'index']) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endforeach; ?>