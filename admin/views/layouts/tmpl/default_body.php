<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <http://www.joomlacomponentbuilder.com>
 * @gitea      Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @github     Joomla Component Builder <https://github.com/vdm-io/Joomla-Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$edit = "index.php?option=com_componentbuilder&view=layouts&task=layout.edit";

?>
<?php foreach ($this->items as $i => $item): ?>
	<?php
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
		$userChkOut = JFactory::getUser($item->checked_out);
		$canDo = ComponentbuilderHelper::getActions('layout',$item,'layouts');
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="order nowrap center hidden-phone">
		<?php if ($canDo->get('core.edit.state')): ?>
			<?php
				$iconClass = '';
				if (!$this->saveOrder)
				{
					$iconClass = ' inactive tip-top" hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
				}
			?>
			<span class="sortable-handler<?php echo $iconClass; ?>">
				<i class="icon-menu"></i>
			</span>
			<?php if ($this->saveOrder) : ?>
				<input type="text" style="display:none" name="order[]" size="5"
				value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
			<?php endif; ?>
		<?php else: ?>
			&#8942;
		<?php endif; ?>
		</td>
		<td class="nowrap center">
		<?php if ($canDo->get('core.edit')): ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					<?php else: ?>
						&#9633;
					<?php endif; ?>
				<?php else: ?>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				<?php endif; ?>
		<?php else: ?>
			&#9633;
		<?php endif; ?>
		</td>
		<td class="nowrap">
			<div>
			<?php if ($canDo->get('core.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->name); ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'layouts.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?><br />
	<code>&lt;?php echo JLayoutHelper::render('<?php echo ComponentbuilderHelper::safeString($item->alias); ?>', [?]); ?&gt;</code>
			</div>
		</td>
		<td class="hidden-phone">
			<div><em>
			<?php echo $this->escape($item->description); ?></em>
			<ul style="list-style: none">
				<li><?php echo JText::_("COM_COMPONENTBUILDER_CUSTOM_PHP"); ?>: <b>
			<?php echo JText::_($item->add_php_view); ?></b></li>
			</ul>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if ($this->user->authorise('dynamic_get.edit', 'com_componentbuilder.dynamic_get.' . (int)$item->dynamic_get)): ?>
					<a href="index.php?option=com_componentbuilder&view=dynamic_gets&task=dynamic_get.edit&id=<?php echo $item->dynamic_get; ?>&return=<?php echo $this->return_here; ?>"><?php echo $this->escape($item->dynamic_get_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->dynamic_get_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="center">
		<?php if ($canDo->get('core.edit.state')) : ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'layouts.', true, 'cb'); ?>
					<?php else: ?>
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'layouts.', false, 'cb'); ?>
					<?php endif; ?>
				<?php else: ?>
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'layouts.', true, 'cb'); ?>
				<?php endif; ?>
		<?php else: ?>
			<?php echo JHtml::_('jgrid.published', $item->published, $i, 'layouts.', false, 'cb'); ?>
		<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>