<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var \Akeeba\Component\AkeebaBackup\Administrator\View\Browser\HtmlView $this */

Text::script('COM_AKEEBABACKUP_CONFIG_UI_ROOTDIR', true);

?>
<?php if(empty($this->folder)): ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_akeebabackup" />
	<input type="hidden" name="view" value="Browser" />
	<input type="hidden" name="format" value="html" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="folder" id="folder" value="" />
	<input type="hidden" name="processfolder" id="processfolder" value="0" />
	<input type="hidden" name="<?= Factory::getApplication()->getFormToken() ?>" value="1" />
</form>
<?php endif ?>

<?php if(!(empty($this->folder))): ?>
<div class="border border-1 border-primary p-2 pt-3 m-1 mb-3">
	<form action="index.php" method="get" name="adminForm" id="adminForm"
	      class="d-flex flex-row align-items-center">

		<div class="me-2 mb-1">
			<span title="<?= Text::_($this->writable ? 'COM_AKEEBABACKUP_CPANEL_LBL_WRITABLE' : 'COM_AKEEBABACKUP_CPANEL_LBL_UNWRITABLE') ?>"
			      class="rounded-2 p-2 text-white <?= $this->writable ? 'bg-success' : 'bg-danger' ?>"
			>
                <span class="<?= $this->writable ? 'fa fa-check-circle' : 'fa fa-ban' ?>"></span>
            </span>
		</div>

		<div class="flex-fill me-2 mb-1">
			<label class="visually-hidden" for="folder">
				Folder
			</label>
			<input type="text" name="folder" id="folder"
			       class="form-control"
			       value="<?= $this->escape($this->folder) ?>" />
		</div>

		<div class="me-2 mb-1">
			<button type="button"
					class="btn btn-primary" id="comAkeebaBrowserGo">
				<span class="fa fa-folder"></span>
				<?= Text::_('COM_AKEEBABACKUP_BROWSER_LBL_GO') ?>
			</button>
		</div>

		<div class="mb-1">
			<button type="button"
					class="btn btn-success" id="comAkeebaBrowserUseThis">
				<span class="fa fa-share"></span>
				<?= Text::_('COM_AKEEBABACKUP_BROWSER_LBL_USE') ?>
			</button>
		</div>

		<input type="hidden" name="folderraw" id="folderraw"
		       value="<?= $this->escape($this->folder_raw) ?>" />
		<input type="hidden" name="<?= Factory::getApplication()->getFormToken() ?>" value="1" />
		<input type="hidden" name="option" value="com_akeebabackup" />
		<input type="hidden" name="view" value="Browser" />
		<input type="hidden" name="tmpl" value="component" />
	</form>
</div>

<?php if(count($this->breadcrumbs)): ?>
<nav aria-label="breadcrumb">
	<ul class="breadcrumb p-3 bg-light">
		<?php $i = 0 ?>
		<?php foreach($this->breadcrumbs as $crumb): ?>
			<?php $i++; ?>
			<li class="breadcrumb-item <?= ($i < count($this->breadcrumbs)) ? '' : 'active' ?>">
				<?php if($i < count($this->breadcrumbs)): ?>
					<a class="text-decoration-none fw-bold"
					   href="<?= $this->escape(Uri::base() . "index.php?option=com_akeebabackup&view=Browser&tmpl=component&folder=" . urlencode($crumb['folder'])) ?>"
					>
						<?= $this->escape($crumb['label']) ?>
					</a>
				<?php else: ?>
					<span class="fw-bold">
						<?= $this->escape($crumb['label']) ?>
					</span>
				<?php endif ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php endif ?>

<div class="border border-1 border-muted rounded-2 p-2">
	<div>
		<?php if(count($this->subfolders)): ?>
		<table class="table table-striped">
			<tr>
				<td>
					<a class="btn btn-dark btn-sm p-2 text-decoration-none"
					   href="<?= $this->escape(Uri::base()) ?>index.php?option=com_akeebabackup&view=Browser&tmpl=component&folder=<?= $this->escape($this->parent) ?>">
						<span class="akion-arrow-up-a"></span>
						<?= Text::_('COM_AKEEBABACKUP_BROWSER_LBL_GOPARENT') ?>
					</a>
				</td>
			</tr>
			<?php foreach($this->subfolders as $subfolder): ?>
			<tr>
				<td>
					<a class="akeeba-browser-folder text-decoration-none" href="<?= $this->escape(Uri::base()) ?>index.php?option=com_akeebabackup&view=Browser&tmpl=component&folder=<?= $this->escape($this->folder . '/' . $subfolder) ?>"><?= $this->escape($subfolder) ?></a>
				</td>
			</tr>
			<?php endforeach ?>
		</table>
		<?php else: ?>
			<?php if(!$this->exists): ?>
			<div class="alert alert-danger">
				<?= Text::_('COM_AKEEBABACKUP_BROWSER_ERR_NOTEXISTS') ?>
			</div>
			<?php elseif(!$this->inRoot): ?>
			<div class="alert alert-warning">
				<?= Text::_('COM_AKEEBABACKUP_BROWSER_ERR_NONROOT') ?>
			</div>
			<?php elseif($this->openbasedirRestricted): ?>
			<div class="alert alert-danger">
				<?= Text::_('COM_AKEEBABACKUP_BROWSER_ERR_BASEDIR') ?>
			</div>
			<?php else: ?>
			<table class="table table-striped">
				<tr>
					<td>
						<a class="btn btn-dark btn-sm p-2 text-decoration-none"
						   href="<?= $this->escape(Uri::base()) ?>index.php?option=com_akeebabackup&view=Browser&tmpl=component&folder=<?= $this->escape($this->parent) ?>">
							<span class="akion-arrow-up-a"></span>
							<?= Text::_('COM_AKEEBABACKUP_BROWSER_LBL_GOPARENT') ?>
						</a>
					</td>
				</tr>
			</table>
			<?php endif // secondary block ?>
		<?php endif // for the count($this->subfolders) block ?>
	</div>
</div>
<?php endif ?>

