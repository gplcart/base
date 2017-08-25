<?php
/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="container">
  <div class="steps">
    <div class="inner">
      <div class="step">
        <div class="btn btn-success btn-circle disabled">1</div>
        <p><?php echo $this->e('Initial configuration'); ?></p>
      </div>
      <div class="step">
        <div class="btn btn-success btn-circle disabled">2</div>
        <p><?php echo $this->e($handler['steps'][1]['title']); ?></p>
      </div>
      <div class="step">
        <div class="btn btn-success btn-circle disabled">3</div>
        <p><?php echo $this->e($handler['steps'][2]['title']); ?></p>
      </div>
      <div class="step">
        <div class="btn btn-primary btn-circle">4</div>
        <p><?php echo $this->e($handler['steps'][3]['title']); ?></p>
      </div>
    </div>
  </div>
  <?php if (!empty($_messages)) { ?>
  <div class="messages">
    <?php foreach ($_messages as $type => $strings) { ?>
    <div class="alert alert-<?php echo $type; ?>">
      <?php foreach ($strings as $string) { ?>
      <?php echo $this->filter($string); ?><br>
      <?php } ?>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($status) { ?>
  <form method="post" data-autosubmit="true">
    <noscript>
      <button class="btn btn-default" name="next" value="1"><?php echo $this->text('Next'); ?></button>
    </noscript>
  </form>
  <?php } ?>
</div>


