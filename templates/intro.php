<?php
/**
 * @package Base
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<h2><?php echo $this->text('Base distribution'); ?></h2>
<p><?php echo $this->text('Your store is mostly set up, but there are a few more steps to take:'); ?></p>
<ol>
  <li><?php echo $this->text('Adjust basic settings for your <a href="@url">main store</a>. If the store is not ready for production, set its status to "Disabled". It allows users with the right permissions to use the site while users without this permission are presented with a message that the site is under maintenance.', array('@url' => $this->url('admin/settings/store/edit/1'))); ?></li>
  <li><?php echo $this->text('Check out <a href="@url">these</a> site-wide settings', array('@url' => $this->url('admin/settings/common'))); ?></li>
  <li>
    <?php echo $this->text('The following <a href="@url">modules</a> use external services and APIs. To get them working you should provide all required credentials:', array('@url' => $this->url('admin/module/list'))); ?>
    <ul>
      <li><a href="<?php echo $this->url('admin/module/settings/authorize'); ?>"><?php echo $this->text('Authorize'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/twocheckout'); ?>"><?php echo $this->text('2 Checkout'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/stripe'); ?>"><?php echo $this->text('Stripe'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/shippo'); ?>"><?php echo $this->text('Shippo'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/ga_report'); ?>"><?php echo $this->text('Google Analytics Report'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/mail'); ?>"><?php echo $this->text('Mail'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/social_login'); ?>"><?php echo $this->text('Social Login'); ?></a></li>
      <li><a href="<?php echo $this->url('admin/module/settings/zopim'); ?>"><?php echo $this->text('Zopim'); ?></a></li>
    </ul>
  </li>
  <li>
    <?php echo $this->text('Configure geographic and locale settings:'); ?>
    <ul>
      <li><?php echo $this->text('Add <a href="@url_zone">geo zones</a>, configure <a href="@url_country">countries</a>, add their states and cities', array('@url_zone' => $this->url('admin/settings/zone'), '@url_country' => $this->url('admin/settings/country'))); ?></li>
      <li><?php echo $this->text('Configure <a href="@url">languages</a>', array('@url' => $this->url('admin/settings/language'))); ?></li>
      <li><?php echo $this->text('Configure <a href="@url_currency">currencies</a> and their <a href="@url_module">exchange rates</a>', array('@url_currency' => $this->url('admin/settings/currency'), '@url_module' => $this->url('admin/module/settings/currency'))); ?></li>
    </ul>
  </li>
  <li><?php echo $this->text('Add more <a href="@url_user">users</a>. You are superadmin and allowed to do everithing on the site. It\'s strongly recommended to create several administrative <a href="@url_role">roles</a> with different permissions (e.g "Boss", "Content manager") and assign them to different users.', array('@url_user' => $this->url('admin/user/list'), '@url_role' => $this->url('admin/user/role'))); ?></li>
</ol>
<a class="btn btn-default" href="<?php echo $this->url('', array('skip_intro' => 1)); ?>">
  <?php echo $this->text('Ok, switch to dashboard'); ?>
</a>