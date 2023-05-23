<?php
/**
 * @Copyright
 *
 * @package jsvisit_counter for Joomla! 2.5 and 3.x
 * @author     Joachim Schmidt {@link http://www.jschmidt-systemberatung.de/}
 * @version Version: 2.0.0 - 5-Feb-2015
 * @link       Project Site {@link http://www.jschmidt-systemberatung.de/}
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  change activity:
 *  22.03.2022: provide horizontal display of counter
 */

// @formatter:off
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

// no direct access
defined('_JEXEC') or die();
$flag_dir = URI::base() . "media/mod_jsvisit_counter/flags/";
?>

<div class="jsvisitcounter<?php echo $moduleclass_sfx ?>">

<table style="width:60%; background: transparent;" align="center">
<tr>
 <td style="width: 30%">

<?php if (count($countries) == 0) { ?>
<div class="table">
 <span class="table_row"></span>
</div>
<?php } ?>
<?php if (count($countries)) : ?>
<div class="<?php echo $layout_class; ?>">
<?php
 foreach ($countries as $country)
 {
  ?>
  <div class="table_row">
   <span class="table-cell33"><img src="<?php echo $flag_dir . $country['flag']; ?>" title="<?php echo $country['count']; ?>" alt="<?php echo Text::_($country['name']); ?>" /></span>
   <span class="table-cell33"><?php echo $country['percent']; ?></span>
   <span class="table-cell33"><?php echo Text::_($country['name']); ?></span>
  </div>
 <?php
 }
 ?>
  <div class="table_row">
   <span class="table-cell33"><br /><strong><?php echo Text::_('MOD_JSVISIT_COUNTER_TOTAL'); ?>:</strong></span>
   <span class="table-cell33"><br /><strong><?php echo $total_countries; ?></strong></span>
   <span class="table-cell33"><br /><strong>
 <?php if ($total_countries > 1)  echo Text::_('MOD_JSVISIT_COUNTER_COUNTRIES');
      else  echo Text::_('MOD_JSVISIT_COUNTER_COUNTRY'); ?></strong></span>
  </div>

 <?php endif; ?>

</td>
<td style="width: 30%; vertical-align: middle;">

<div class="table" style="width: 100%; background: transparent;">
 <div class="counter"><?php echo $counter; ?></div>
</div>

</td>
<td style="width: 30%">

<?php
 if ($today || $yesterday || $this_week || $last_week || $this_month || $last_month)
   echo "<div class='" . $layout_class ."' style='width: 80%;'>";
 else
   echo "<div>";
 ?>

<?php if ($today) : ?>
 <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_TODAY'); ?>:</span>
   <span class="col-right"><?php echo $today; ?></span>
  </div>
<?php endif; ?>
  
<?php if ($yesterday) : ?>
 <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_YESTERDAY'); ?>:</span>
   <span class="col-right"><?php echo $yesterday; ?></span>
  </div>
<?php endif; ?>

<?php if ($this_week) : ?>
  <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_THIS_WEEK'); ?>:</span>
   <span class="col-right"><?php echo $this_week; ?></span>
  </div>
<?php endif; ?>
 
<?php if ($last_week) : ?>
  <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_LAST_WEEK'); ?>:</span>
   <span class="col-right"><?php echo $last_week; ?></span>
  </div>
<?php endif; ?>

<?php if ($this_month) : ?>
  <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_THIS_MONTH'); ?>:</span>
   <span class="col-right"><?php echo $this_month; ?></span>
  </div>
<?php endif; ?>
 
<?php if ($last_month) : ?>
 <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_LAST_MONTH'); ?>:</span>
   <span class="col-right"><?php echo $last_month; ?></span>
  </div>
<?php endif; ?>

<?php if ($totals) : ?>
 <div class="table_row">
   <span class="col-left"><?php echo Text::_('MOD_JSVISIT_COUNTER_TOTAL'); ?>:</span>
   <span class="col-right"><?php echo $totals; ?></span>
  </div>
<?php endif; ?>

</td></tr></table>
</div>
