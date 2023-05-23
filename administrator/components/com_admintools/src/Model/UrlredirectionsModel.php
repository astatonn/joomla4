<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AdminTools\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

#[\AllowDynamicProperties]
class UrlredirectionsModel extends ListModel
{
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		$config['filter_fields'] = $config['filter_fields'] ?? [];
		$config['filter_fields'] = $config['filter_fields'] ?: [
			'search',
			'id',
			'source',
			'dest',
			'ordering',
			'published',
			'keepurlparams',
		];

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'id', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . 'filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', ($published === '') ? $published : (int) $published);

		$keepurlparams = $app->getUserStateFromRequest($this->context . 'filter.keepurlparams', 'filter_keepurlparams', '', 'string');
		$this->setState('filter.keepurlparams', ($keepurlparams === '') ? $keepurlparams : (int) $keepurlparams);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.keepurlparams');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__admintools_redirects'));

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (substr($search, 0, 3) === 'id:')
			{
				$id = (int) substr($search, 3);

				$query->where($db->quoteName('id') . ' = :id')
					->bind(':id', $id, ParameterType::INTEGER);
			}
			elseif (substr($search, 0, 6) === 'visit:')
			{
				$search = '%' . substr($search, 6) . '%';

				$query->where($db->quoteName('dest') . ' LIKE :search')
					->bind(':search', $search, ParameterType::STRING);

			}
			elseif (substr($search, 0, 5) === 'goto:')
			{
				$search = '%' . substr($search, 5) . '%';

				$query->where($db->quoteName('source') . ' LIKE :search')
					->bind(':search', $search, ParameterType::STRING);
			}
			else
			{
				$search  = '%' . $search . '%';
				$search2 = $search;

				$query->where('(' .
					implode(') OR (', [
						$db->quoteName('source') . ' LIKE :search',
						$db->quoteName('dest') . ' LIKE :search2',
					])
					. ')')
					->bind(':search', $search, ParameterType::STRING)
					->bind(':search2', $search2, ParameterType::STRING);
			}
		}

		$published = $this->getState('filter.published');

		if (is_null($published))
		{
			$query->where($db->quoteName('published') . ' = :published')
				->bind(':published', $published, ParameterType::INTEGER);
		}

		$keepurlparams = $this->getState('filter.keepurlparams');

		if (is_null($keepurlparams))
		{
			$query->where($db->quoteName('keepurlparams') . ' = :keepurlparams')
				->bind(':keepurlparams', $keepurlparams, ParameterType::INTEGER);
		}

		// List ordering clause
		$orderCol  = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$ordering  = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);

		return $query;
	}


}