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

/**
 * Componentbuilder View class for the Libraries
 */
class ComponentbuilderViewLibraries extends JViewLegacy
{
	/**
	 * Libraries view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			ComponentbuilderHelper::addSubmenu('libraries');
		}

		// Assign data to the view
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->user = JFactory::getUser();
		// Load the filter form from xml.
		$this->filterForm = $this->get('FilterForm');
		// Load the active filters.
		$this->activeFilters = $this->get('ActiveFilters');
		// Add the list ordering clause.
		$this->listOrder = $this->escape($this->state->get('list.ordering', 'a.id'));
		$this->listDirn = $this->escape($this->state->get('list.direction', 'desc'));
		$this->saveOrder = $this->listOrder == 'a.ordering';
		// set the return here value
		$this->return_here = urlencode(base64_encode((string) JUri::getInstance()));
		// get global action permissions
		$this->canDo = ComponentbuilderHelper::getActions('library');
		$this->canEdit = $this->canDo->get('library.edit');
		$this->canState = $this->canDo->get('library.edit.state');
		$this->canCreate = $this->canDo->get('library.create');
		$this->canDelete = $this->canDo->get('library.delete');
		$this->canBatch = $this->canDo->get('core.batch');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
			// load the batch html
			if ($this->canCreate && $this->canEdit && $this->canState)
			{
				$this->batchDisplay = JHtmlBatch_::render();
			}
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_COMPONENTBUILDER_LIBRARIES'), 'puzzle');
		JHtmlSidebar::setAction('index.php?option=com_componentbuilder&view=libraries');
		JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

		if ($this->canCreate)
		{
			JToolBarHelper::addNew('library.add');
		}

		// Only load if there are items
		if (ComponentbuilderHelper::checkArray($this->items))
		{
			if ($this->canEdit)
			{
				JToolBarHelper::editList('library.edit');
			}

			if ($this->canState)
			{
				JToolBarHelper::publishList('libraries.publish');
				JToolBarHelper::unpublishList('libraries.unpublish');
				JToolBarHelper::archiveList('libraries.archive');

				if ($this->canDo->get('core.admin'))
				{
					JToolBarHelper::checkin('libraries.checkin');
				}
			}

			// Add a batch button
			if ($this->canBatch && $this->canCreate && $this->canEdit && $this->canState)
			{
				// Get the toolbar object instance
				$bar = JToolBar::getInstance('toolbar');
				// set the batch button name
				$title = JText::_('JTOOLBAR_BATCH');
				// Instantiate a new JLayoutFile instance and render the batch button
				$layout = new JLayoutFile('joomla.toolbar.batch');
				// add the button to the page
				$dhtml = $layout->render(array('title' => $title));
				$bar->appendButton('Custom', $dhtml, 'batch');
			}

			if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete))
			{
				JToolbarHelper::deleteList('', 'libraries.delete', 'JTOOLBAR_EMPTY_TRASH');
			}
			elseif ($this->canState && $this->canDelete)
			{
				JToolbarHelper::trash('libraries.trash');
			}
		}
		if ($this->user->authorise('library.get_snippets', 'com_componentbuilder'))
		{
			// add Get Snippets button.
			JToolBarHelper::custom('libraries.getSnippets', 'search custom-button-getsnippets', '', 'COM_COMPONENTBUILDER_GET_SNIPPETS', false);
		}

		// set help url for this view if found
		$help_url = ComponentbuilderHelper::getHelpUrl('libraries');
		if (ComponentbuilderHelper::checkString($help_url))
		{
				JToolbarHelper::help('COM_COMPONENTBUILDER_HELP_MANAGER', false, $help_url);
		}

		// add the options comp button
		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_componentbuilder');
		}

		// Only load published batch if state and batch is allowed
		if ($this->canState && $this->canBatch)
		{
			JHtmlBatch_::addListSelection(
				JText::_('COM_COMPONENTBUILDER_KEEP_ORIGINAL_STATE'),
				'batch[published]',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('all' => false)), 'value', 'text', '', true)
			);
		}

		// Only load access batch if create, edit and batch is allowed
		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			JHtmlBatch_::addListSelection(
				JText::_('COM_COMPONENTBUILDER_KEEP_ORIGINAL_ACCESS'),
				'batch[access]',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text')
			);
		}

		// Only load Target batch if create, edit, and batch is allowed
		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			// Set Target Selection
			$this->targetOptions = JFormHelper::loadFieldType('librariesfiltertarget')->options;
			// We do some sanitation for Target filter
			if (ComponentbuilderHelper::checkArray($this->targetOptions) &&
				isset($this->targetOptions[0]->value) &&
				!ComponentbuilderHelper::checkString($this->targetOptions[0]->value))
			{
				unset($this->targetOptions[0]);
			}
			// Target Batch Selection
			JHtmlBatch_::addListSelection(
				'- Keep Original '.JText::_('COM_COMPONENTBUILDER_LIBRARY_TARGET_LABEL').' -',
				'batch[target]',
				JHtml::_('select.options', $this->targetOptions, 'value', 'text')
			);
		}

		// Only load How batch if create, edit, and batch is allowed
		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			// Set How Selection
			$this->howOptions = JFormHelper::loadFieldType('Filebehaviour')->options;
			// We do some sanitation for How filter
			if (ComponentbuilderHelper::checkArray($this->howOptions) &&
				isset($this->howOptions[0]->value) &&
				!ComponentbuilderHelper::checkString($this->howOptions[0]->value))
			{
				unset($this->howOptions[0]);
			}
			// How Batch Selection
			JHtmlBatch_::addListSelection(
				'- Keep Original '.JText::_('COM_COMPONENTBUILDER_LIBRARY_HOW_LABEL').' -',
				'batch[how]',
				JHtml::_('select.options', $this->howOptions, 'value', 'text')
			);
		}

		// Only load Type batch if create, edit, and batch is allowed
		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			// Set Type Selection
			$this->typeOptions = JFormHelper::loadFieldType('librariesfiltertype')->options;
			// We do some sanitation for Type filter
			if (ComponentbuilderHelper::checkArray($this->typeOptions) &&
				isset($this->typeOptions[0]->value) &&
				!ComponentbuilderHelper::checkString($this->typeOptions[0]->value))
			{
				unset($this->typeOptions[0]);
			}
			// Type Batch Selection
			JHtmlBatch_::addListSelection(
				'- Keep Original '.JText::_('COM_COMPONENTBUILDER_LIBRARY_TYPE_LABEL').' -',
				'batch[type]',
				JHtml::_('select.options', $this->typeOptions, 'value', 'text')
			);
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_('COM_COMPONENTBUILDER_LIBRARIES'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_componentbuilder/assets/css/libraries.css", (ComponentbuilderHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if(strlen($var) > 50)
		{
			// use the helper htmlEscape method instead and shorten the string
			return ComponentbuilderHelper::htmlEscape($var, $this->_charset, true);
		}
		// use the helper htmlEscape method instead.
		return ComponentbuilderHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.name' => JText::_('COM_COMPONENTBUILDER_LIBRARY_NAME_LABEL'),
			'a.target' => JText::_('COM_COMPONENTBUILDER_LIBRARY_TARGET_LABEL'),
			'a.type' => JText::_('COM_COMPONENTBUILDER_LIBRARY_TYPE_LABEL'),
			'a.description' => JText::_('COM_COMPONENTBUILDER_LIBRARY_DESCRIPTION_LABEL'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
