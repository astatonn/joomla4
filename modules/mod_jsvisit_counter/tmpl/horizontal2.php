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

<table class="<?php echo $layout_class; ?>" style="width:40%; background: transparent;" align="center">
<tr>
 <td style="width: 50%">

<?php if (count($countries) == 0) { ?>
<div class="table">
 <span class="table_row"></span>
</div>
<?php } ?>
<?php if (count($countries)) : ?>
<div style="margin:5px;">
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
<td style="width: auto; vertical-align: middle;">

<div class="table" style="width: 20%; background: transparent;">
 <div class="counter"><?php echo $counter; ?></div>
</div>
</td></tr></table>
</div>

<?php
 if ($today || $yesterday || $this_week || $last_week || $this_month || $last_month)
 {
   if  ($layout_class == 'boxed')
    echo "<div style='border:2px solid #a0a0a0; padding:8px; width:fit-content;margin-left:auto;margin-right:auto;'>";
 }
 else
   echo "<div>";
 ?>

<?php if ($today) : ?>
   <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_TODAY'); ?>:
   </b>&nbsp;<?php echo $today; ?></span>
<?php endif; ?>
  
<?php if ($yesterday) : ?>
   <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_YESTERDAY'); ?>:
   </b>&nbsp;<?php echo $yesterday; ?></span>
 <?php endif; ?>

<?php if ($this_week) : ?>
     <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_THIS_WEEK'); ?>:
    </b>&nbsp;<?php echo $this_week; ?></span>
<?php endif; ?>
 
<?php if ($last_week) : ?>
     <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_LAST_WEEK'); ?>:
   </b>&nbsp;<?php echo $last_week; ?></span>
<?php endif; ?>

<?php if ($this_month) : ?>
   <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_THIS_MONTH'); ?>:
   </b>&nbsp;<?php echo $this_month; ?></span>
 <?php endif; ?>
 
<?php if ($last_month) : ?>
    <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_LAST_MONTH'); ?>:
   </b>&nbsp;<?php echo $last_month; ?></span>
<?php endif; ?>

<?php if ($totals) : ?>
   <span class="horizontal-left"><b><?php echo Text::_('MOD_JSVISIT_COUNTER_TOTAL'); ?>:
   </b>&nbsp;<?php echo $totals; ?></span>
<?php endif; ?>

</div>
