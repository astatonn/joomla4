<?php
/**
* @package SideScript Module for Joomla 3.0 By Joomlatema.net
 * @version $Id: mod_SideScript_jt1.php  2013-25-05  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2013- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
require_once dirname(__FILE__) . DS . 'helper.php';
jimport('joomla.document.html.renderer.module');
//
//styling
$panel_width= $params->get( 'panel_width' );
$background = $params->get( 'background' );
$show_heading= $params->get( 'show_heading' );
$heading= $params->get( 'heading' );
$header_border = $params->get( 'header_border' );
$header_border_radius = $params->get( 'header_border_radius' );
$header_background = $params->get( 'header_background' );
$headercolor = $params->get( 'headercolor' );
$border = $params->get( 'border' );
$border_radius= $params->get( 'border_radius' );
$topposition = $params->get( 'topposition' );
$leftposition = $params->get( 'leftposition' );
$panel_position= $params->get( 'panel_position');
$fixedposition= $params->get( 'fixedposition' );
$css_top=$params->get('panel_position') == 'top';
$css_right=$params->get('panel_position') == 'right';
$css_left=$params->get('panel_position') == 'left';
$css_bottom=$params->get('panel_position') == 'bottom';
$padding= $params->get('padding');
$showtitle= $params->get('showtitle');
//

$moduleid= $params->get( 'id' );
$module_file =$params->get( 'module_mode' )  == 'modules';
$htmlcontent =$params->get( 'html_content' );
//
$document = JFactory::getDocument();
$document->addScript('modules/mod_popup_jt/js/jquery-ui.js');
//
$mod_attrs = array('style' => 'xhtml');

//
$document->addStyleSheet(JURI::base() . 'modules/mod_popup_jt/css/style.css');
$show_effect=$params->get( 'show_effect' );
$show_duration=$params->get( 'show_duration' );

$hide_effect=$params->get( 'hide_effect' );
$hide_duration=$params->get( 'hide_duration' );
// show effect
if ($params->get('show_effect') == 'drop_up') {
$show="{ effect: 'drop',direction: 'up', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'drop_down') {
$show="{ effect: 'drop',direction: 'down', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'drop_left') {
$show="{ effect: 'drop',direction: 'left', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'drop_right') {
$show="{ effect: 'drop',direction: 'right', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'clip_vertical') {
$show="{ effect: 'clip',direction: 'vertical', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'clip_horizontal') {
$show="{ effect: 'clip',direction: 'horizontal', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'blind_up') {
$show="{ effect: 'blind',direction: 'up', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'blind_down') {
$show="{ effect: 'blind',direction: 'down', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'blind_left') {
$show="{ effect: 'blind',direction: 'left', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'blind_right') {
$show="{ effect: 'blind',direction: 'right', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'slide_up') {
$show="{ effect: 'slide',direction: 'up', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'slide_down') {
$show="{ effect: 'slide',direction: 'down', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'slide_left') {
$show="{ effect: 'slide',direction: 'left', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'slide_right') {
$show="{ effect: 'slide',direction: 'right', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'shake_up') {
$show="{ effect: 'shake',direction: 'up', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'shake_down') {
$show="{ effect: 'shake',direction: 'down', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'shake_left') {
$show="{ effect: 'shake',direction: 'left', duration:$show_duration }";
}
else if ($params->get('show_effect') == 'shake_right') {
$show="{ effect: 'shake',direction: 'right', duration:$show_duration }";
}

else {
$show="{ effect: '$show_effect', duration:$show_duration }";
}
//hide effect

if ($params->get('hide_effect') == 'drop_up') {
$hide="{ effect: 'drop',direction: 'up', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'drop_down') {
$hide="{ effect: 'drop',direction: 'down', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'drop_left') {
$hide="{ effect: 'drop',direction: 'left', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'drop_right') {
$hide="{ effect: 'drop',direction: 'right', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'clip_vertical') {
$hide="{ effect: 'clip',direction: 'vertical', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'clip_horizontal') {
$hide="{ effect: 'clip',direction: 'horizontal', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'blind_up') {
$hide="{ effect: 'blind',direction: 'up', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'blind_down') {
$hide="{ effect: 'blind',direction: 'down', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'blind_left') {
$hide="{ effect: 'blind',direction: 'left', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'blind_right') {
$hide="{ effect: 'blind',direction: 'right', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'slide_up') {
$hide="{ effect: 'slide',direction: 'up', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'slide_down') {
$hide="{ effect: 'slide',direction: 'down', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'slide_left') {
$hide="{ effect: 'slide',direction: 'left', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'slide_right') {
$hide="{ effect: 'slide',direction: 'right', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'shake_up') {
$hide="{ effect: 'shake',direction: 'up', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'shake_down') {
$hide="{ effect: 'shake',direction: 'down', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'shake_left') {
$hide="{ effect: 'shake',direction: 'left', duration:$hide_duration }";
}
else if ($params->get('hide_effect') == 'shake_right') {
$hide="{ effect: 'shake',direction: 'right', duration:$hide_duration }";
}

else {
$hide="{ effect: '$hide_effect', duration:$hide_duration }";
}

/////
if ($params->get('showtitle') == 0) {
$showclosebutton='none';
}
else {
$showclosebutton='';
}
/////////////////
$runOnce= $params->get( 'runOnce' );
$autoOpen= $params->get( 'autoOpen' );
if ($params->get('runOnce') == 'true') {
$autoOpen='false';
}
else {
$autoOpen= $params->get( 'autoOpen' );
}
?>
<style type="text/css">
/* Corner radius */
.ui-corner-all, .ui-corner-top, .ui-corner-left, .ui-corner-tl { -moz-border-radius-topleft: <?php echo $border_radius;?>; -webkit-border-top-left-radius: <?php echo $border_radius;?>; -khtml-border-top-left-radius: <?php echo $border_radius;?>; border-top-left-radius: <?php echo $border_radius;?>; }
.ui-corner-all, .ui-corner-top, .ui-corner-right, .ui-corner-tr { -moz-border-radius-topright: <?php echo $border_radius;?>; -webkit-border-top-right-radius: <?php echo $border_radius;?>; -khtml-border-top-right-radius: <?php echo $border_radius;?>; border-top-right-radius: <?php echo $border_radius;?>; }
.ui-corner-all, .ui-corner-bottom, .ui-corner-left, .ui-corner-bl { -moz-border-radius-bottomleft: <?php echo $border_radius;?>; -webkit-border-bottom-left-radius: <?php echo $border_radius;?>; -khtml-border-bottom-left-radius: <?php echo $border_radius;?>; border-bottom-left-radius: <?php echo $border_radius;?>; }
.ui-corner-all, .ui-corner-bottom, .ui-corner-right, .ui-corner-br { -moz-border-radius-bottomright: <?php echo $border_radius;?>; -webkit-border-bottom-right-radius: <?php echo $border_radius;?>; -khtml-border-bottom-right-radius: <?php echo $border_radius;?>; border-bottom-right-radius: <?php echo $border_radius;?>; }
.ui-widget-content { border:<?php echo $border;?>;  background-color: <?php echo $background;?>;}
.ui-widget-header  { border-radius:<?php echo $header_border_radius;?>; display:block;border: <?php echo $header_border;?>; background: <?php echo $header_background;?>;  font-weight: bold; color:<?php echo $headercolor;?>; }
.ui-dialog .ui-dialog-titlebar{ display:<?php echo $showclosebutton;?>;}
.ui-dialog-titlebar.ui-widget-header.popupjt{ display:block;}
</style>
	<?php 
 require( JModuleHelper::getLayoutPath( 'mod_popup_jt' , $params->get('layout', 'default')) );
 ?>

	<script type="text/javascript">
	jQuery.noConflict();
jQuery( document ).ready(function( jQuery ) {
			jQuery("#popup").dialog({
				autoOpen:<?php echo $autoOpen;?>,
				position: { my: "<?php echo $params->get( 'horizontal_position' );?> <?php echo $params->get( 'vertical_position' );?>", at: "<?php echo $params->get( 'horizontal_position' );?> <?php echo $params->get( 'vertical_position' );?>", of: window },
				hide: <?php echo $hide;?>, 
				show: <?php echo $show;?>, 
                modal: <?php echo $params->get( 'modal' );?>,
				resizable: <?php echo $params->get( 'resizable' );?>,
				title:"<?php echo $params->get( 'popuptitle' );?>",
				width: "<?php echo $params->get( 'popupwidth' );?>",
				draggable: <?php echo $params->get( 'draggable' );?>,
				height: "<?php echo $params->get( 'popupheight' );?>",
				closeOnEscape:<?php echo $params->get( 'closeOnEscape' );?>,
				open: function(event, ui){
     setTimeout("jQuery('#popup').dialog('close')",<?php echo $params->get( 'time_to_close' );?>);
    }
			});
						
			jQuery("#button").on("click", function() {
				jQuery("#popup").dialog("open");
				  
			});
			jQuery(".ui-dialog-titlebar-close").on("click",function(){
				 jQuery("#popup").dialog("close");
				 
		});
jQuery( "#opener" ).click(function() {
               jQuery( "#popup" ).dialog( "open" );
            });
		if( ! sessionStorage.getItem( "runOnce" ) ){
			jQuery( "#popup" ).dialog( "open" );
			sessionStorage.setItem( "runOnce", true );
};
		});
	</script>