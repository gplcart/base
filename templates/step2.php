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
        <div class="btn btn-primary btn-circle">3</div>
        <p><?php echo $this->e($handler['steps'][2]['title']); ?></p>
      </div>
      <div class="step">
        <div class="btn btn-default btn-circle disabled">4</div>
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
  <form method="post">
    <div class="radio">
      <label>
        <input type="radio" name="step[demo_handler_id]" value="" checked>
        <?php echo $this->text('No demo'); ?>
      </label>
    </div>
    <?php foreach ($demo_handlers as $handler_id => $handler) { ?>
    <div class="radio">
      <label>
        <input type="radio" name="step[demo_handler_id]" value="<?php echo $this->e($handler_id); ?>">
        <?php echo $this->e($handler['title']); ?>
        <?php if (!empty($handler['description'])) { ?>
        <span class="help-block"><?php echo $this->text('Please select a demo content package. You can delete/add it anytime after the installation'); ?></span>
        <?php } ?>
      </label>
    </div>
    <?php } ?>
    <div class="help-block"><?php echo $this->error('handler_id'); ?></div>
    <button class="btn btn-default" name="next" value="1"><?php echo $this->text('Next'); ?></button>
  </form>
  <?php } ?>
</div>


