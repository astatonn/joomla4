<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Akeeba\Component\AdminTools\Administrator\View\Urlredirections\HtmlView $this */

HTMLHelper::_('behavior.multiselect');

$app               = Factory::getApplication();
$user              = $app->getIdentity();
$userId            = $user->get('id');
$listOrder         = $this->escape($this->state->get('list.ordering'));
$listDirn          = $this->escape($this->state->get('list.direction'));
$nullDate          = Factory::getDbo()->getNullDate();
$hasCategoryFilter = !empty($this->getModel()->getState('filter.category_id'));
$saveOrder         = $listOrder == 'ordering';
$baseUri           = Uri::root();

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_admintools&task=urlredirections.saveOrderAjax&tmpl=component&' . $app->getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$i = 0;

?>

<form action="<?= Route::_('index.php?option=com_admintools&view=Urlredirections'); ?>"
	  method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span
								class="visually-hidden"><?= Text::_('INFO'); ?></span>
						<?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="articleList">
						<caption class="visually-hidden">
							<?= Text::_('COM_ADMINTOOLS_URLREDIRECTIONS_TABLE_CAPTION'); ?>, <span
									id="orderedBy"><?= Text::_('JGLOBAL_SORTED_BY'); ?> </span>, <span
									id="filteredBy"><?= Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?= HTMLHelper::_('grid.checkall'); ?>
							</td>

							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'i.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
							</th>

							<th scope="col">
								<?= HTMLHelper::_('searchtools.sort', 'COM_ADMINTOOLS_REDIRECTION_LBL_DEST', 'dest', $listDirn, $listOrder); ?>
							</th>

							<th scope="col" class="d-none d-md-table-cell">
								<?= HTMLHelper::_('searchtools.sort', 'COM_ADMINTOOLS_REDIRECTION_LBL_SOURCE', 'source', $listDirn, $listOrder); ?>
							</th>

							<th scope="col" class="d-none d-md-table-cell">
								<?= HTMLHelper::_('searchtools.sort', 'COM_ADMINTOOLS_REDIRECTION_LBL_KEEPURLPARAMS', 'keepurlparams', $listDirn, $listOrder); ?>
							</th>

							<th scope="col">
								<?= Text::_('JPUBLISHED') ?>
							</th>

							<th scope="col" class="w-1 d-none d-md-table-cell">
								<?= HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
						<?php foreach ($this->items as $item) : ?>
							<?php
							$canEdit    = $user->authorise('core.edit', 'com_admintools');
							$canCheckin = $user->authorise('core.manage', 'com_checkin')
								|| $item->checked_out == $userId || is_null($item->checked_out);
							$canEditOwn = $user->authorise('core.edit.own', 'com_admintools') && $item->created_by == $userId;
							$canChange  = $user->authorise('core.edit.state', 'com_admintools') && $canCheckin;

							$keepParamsClass = ($item->keepurlparams == 0) ? 'text-success' : (($item->keepurlparams == 1) ? 'text-danger' : 'text-secondary');
							?>
							<tr class="row<?= $i++ % 2; ?>">
								<td class="text-center">
									<?= HTMLHelper::_('grid.id', $i, $item->id, !(empty($item->checked_out_time) || ($item->checked_out_time === $nullDate)), 'cid', 'cb', $item->dest); ?>
								</td>

								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';

									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-ellipsis-v" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" name="order[]" size="5"
											   value="<?php echo $item->ordering; ?>"
											   class="width-20 text-area-order hidden">
									<?php endif; ?>
								</td>

								<td>
									<?php if ($canEdit): ?>
										<a href="<?= Route::_('index.php?option=com_admintools&task=urlredirection.edit&id=' . (int) $item->id); ?>"
										   title="<?= Text::_('JACTION_EDIT'); ?>">
											<span class="text-muted"><?= $baseUri ?></span><strong><?= $this->escape($item->dest); ?></strong>
										</a>
									<?php else: ?>
										<span class="text-muted"><?= $baseUri ?></span><strong><?= $this->escape($item->dest); ?></strong>
									<?php endif ?>
								</td>

								<td class="d-none d-md-table-cell">
									<a href="<?= $item->source ?>" target="_blank">
										<?= $this->escape($item->source) ?>
									</a>
								</td>

								<td class="d-none d-md-table-cell <?= $keepParamsClass ?>">
									<?= Text::_('COM_ADMINTOOLS_REDIRECTION_LBL_KEEPURLPARAMS_' . ($item->keepurlparams == 0 ? 'OFF' : ($item->keepurlparams == 1 ? 'ALL' : 'ADD'))) ?>
								</td>

								<td class="text-center">
									<?= HTMLHelper::_('jgrid.published', $item->published, $i, 'urlredirections.', $user->authorise('core.edit.state', 'com_admintools'), 'cb'); ?>
								</td>

								<td class="w-1 d-none d-md-table-cell">
									<?= $item->id ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // Load the pagination. ?>
					<?= $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value=""> <input type="hidden" name="boxchecked" value="0">
				<?= HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
