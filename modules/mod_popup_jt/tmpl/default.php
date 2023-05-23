<?php 
/**
# ------------------------------------------------------------------------
# Extensions for Joomla 2.5.x - Joomla 3.x
# ------------------------------------------------------------------------
 * @package Jumpmenu Module for Joomla! 
 * @version $Id: 1.5 
 * @author JoomlaTema
 * @Copyright (C) 2012- JoomlaTema
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 # ------------------------------------------------------------------------
**/
defined( '_JEXEC' ) or die( 'Restricted access' ); 
$layout_style= $params->get('layout_style');
?>
<div id="popup">
	<?php if ($showtitle==0) : ?>
<div class="header popupjt"><button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close popupjt" title="ESC ou Fechar"><span class="ui-button-icon ui-icon ui-icon-closethick popupjt"></span><span class="ui-button-icon-space"> </span>Close</button></div><?php endif; ?>
		<div class="content" style="padding:<?php echo $padding;?>;"> 
		<?php if ($module_file) { ?>
<div id="modpos">


							<div style="clear:both; margin:10px 0;" class="sidescript_block <?php echo $extClass; ?>" >
<?php 
jimport('joomla.application.module.helper');
foreach ($moduleid as $module){
$modules = JModuleHelper::getModuleById($module);
echo JModuleHelper::renderModule($modules); 
}
	?>
							</div>
							</div>
<?php } else { ?>
   <div id="htmlcontent"><?php echo $htmlcontent; ?></div>
<?php } ?>
</div>
	</div>