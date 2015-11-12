<?php
/**
 * Plugin element to render Joomla's tags field
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.tags
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/plugins/fabrik_element/databasejoin/databasejoin.php';

/**
 * Plugin element to render Joomla's tags field
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.tags
 * @since       3.0
 */
class PlgFabrik_ElementTags extends PlgFabrik_ElementDatabasejoin
{
	/**
	 * Multi-db join option - can we add duplicate options (set to false in tags element)
	 * @var  bool
	 */
	protected $allowDuplicates = false;

	/**
	 * Load element params
	 *
	 * @return  object  default element params
	 */
	public function getParams()
	{
		if (!isset($this->params))
		{
			$this->params = new JRegistry($this->getElement()->params);
			$this->params->set('table_join', '#__tags');
		}

		return $this->params;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */
	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->rowid = $this->getFormModel()->getRowId();
		$opts->id = $this->id;

		return array('FbTags', $id, $opts);
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To pre-populate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */
	public function render($data, $repeatCounter = 0)
	{
		$str = array();
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$name = $this->getHTMLName($repeatCounter);

		if ($this->isEditable())
		{
			$tmp = $this->_getOptions($data, $repeatCounter, true);

			// Include jQuery
			JHtml::_('jquery.framework');

			// Requires chosen to work
			JText::script('JGLOBAL_KEEP_TYPING');
			JText::script('JGLOBAL_LOOKING_FOR');
			JText::script('JGLOBAL_SELECT_SOME_OPTIONS');
			JText::script('JGLOBAL_SELECT_AN_OPTION');
			JText::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');

			$ext = FabrikHelperHTML::isDebug() ? '.min.js' : '.js';
			JHtml::_('script', 'jui/chosen.jquery' . $ext, false, true, false, false);
			JHtml::_('stylesheet', 'jui/chosen.css', false, true);
			JHtml::_('script', 'jui/ajax-chosen' . $ext, false, true, false, false);

			$bootstrapClass = $params->get('bootstrap_class', 'span12');
			$attr = 'multiple="multiple" class="inputbox ' . $bootstrapClass. ' small"';
			$selected = $tmp;
			$str[] = JHtml::_('select.genericlist', $tmp, $name, trim($attr), 'value', 'text', $selected, $id);

			return implode("\n", $str);
		}
		else
		{
			$tmp = $this->_getOptions($data, $repeatCounter, true);

			$d = array();

			foreach ($tmp as $o)
			{
				$d[$o->value] = $o->text;
			}

			$name = $this->getFullName(true, false);
			$baseUrl = $this->tagUrl();
			$icon = $this->tagIcon();
			$data = FabrikHelperHTML::tagify($d, $baseUrl, $name, $icon);

			return implode("\n", $data);
		}
	}

	/**
	 * Create the where part for the query that selects the list options
	 *
	 * @param   array           $data            Current row data to use in placeholder replacements
	 * @param   bool            $incWhere        Should the additional user defined WHERE statement be included
	 * @param   string          $thisTableAlias  Db table alias
	 * @param   array           $opts            Options
	 * @param   JDatabaseQuery  $query           Append where to JDatabaseQuery object or return string (false)
	 *
	 * @return string|JDatabaseQuery
	 */
	protected function buildQueryWhere($data = array(), $incWhere = true, $thisTableAlias = null, $opts = array(), $query = false)
	{
		$rowId = $this->getFormModel()->getRowId();
		$db = $this->getDb();
		$join = $this->getJoin();
		$fk = $db->qn($join->table_join_alias . '.' . $join->table_join_key);
		$params = $this->getParams();
		$formModel = $this->getFormModel();

		// Always filter on the current records tags (return no records if new row)
		$params->set('database_join_where_access', 1);

		if ($formModel->failedValidation())
		{
			$pk = $db->qn($join->table_join_alias . '.' . $join->table_key);
			$name = $this->getFullName(true, false) . '_raw';
			$tagIds = FArrayHelper::getValue($data, $name, array());
			JArrayHelper::toInteger($tagIds);
			$where = empty($tagIds) ? '6 = -6' : $pk . ' IN (' . implode(', ', $tagIds) . ')';
		}
		else
		{
			// $$$ hugh - erm ... surely we don't want to select ALL tags on a new form?
			/*
			if (!empty($rowId))
			{
				$where = $fk . ' = ' . $db->quote($rowId);
			}
			else
			{
				$where = '';
			}
			*/
			if (FArrayHelper::getValue($opts, 'mode', '') !== 'filter')
			{
				$where = $fk . ' = ' . $db->quote($rowId);
			}
			else
			{
				$where = '';
			}
		}

		$params->set('database_join_where_sql',  $where);

		$where = parent::buildQueryWhere($data, $incWhere, $thisTableAlias, $opts, $query);

		return $where;
	}

	/**
	 * If buildQuery needs additional joins then set them here
	 *
	 * @param   mixed  $query  false to return string, or JQueryBuilder object
	 *
	 * @since 3.0rc1
	 *
	 * @return string|JQueryerBuilder join statement to add
	 */
	protected function buildQueryJoin($query = false)
	{
		$db = $this->getDb();
		$f = $db->qn($this->getJoin()->table_join_alias . '.tags');

		if ($query !== false)
		{
			$query->join('LEFT', '#__tags AS t ON t.id = ' . $f);

			return $query;
		}
	}

	/**
	 * Do you add a please select option to the cdd list
	 *
	 * @since 3.0b
	 *
	 * @return boolean
	 */
	protected function showPleaseSelect()
	{
		return false;
	}

	/**
	 * Get database field description
	 *
	 * @return  string  db field type
	 */
	public function getFieldDescription()
	{
		return "INT(11)";
	}

	/**
	 * Does the element store its data in a join table (1:n)
	 *
	 * @return	bool
	 */
	public function isJoin()
	{
		return true;
	}

	/**
	 * Get the join to database name
	 *
	 * @return  string	database name
	 */
	protected function getDbName()
	{
		$this->dbname = '#__tags';

		return $this->dbname;
	}

	/**
	 * Get the field name for the joined tables' pk
	 *
	 * @return  string
	 */
	protected function getJoinValueFieldName()
	{
		return 'id';
	}

	/**
	 * Get the label parameter's value
	 *
	 * @return string
	 */
	protected function getLabelParamVal()
	{
		if (!isset($this->labelParamVal))
		{
			$this->labelParamVal = 'title';
		}

		return $this->labelParamVal;
	}

	/**
	 * Used by elements with suboptions, given a value, return its label
	 *
	 * @param   string  $v             Value
	 * @param   string  $defaultLabel  Default label
	 * @param   bool    $forceCheck    Force check even if $v === $defaultLabel
	 *
	 * @return  string	Label
	 */
	public function getLabelForValue($v, $defaultLabel = null, $forceCheck = false)
	{
		// Band aid - as this is called in listModel::addLabels() lets not bother - re-querying the db (label already loaded)
		if ($v === $defaultLabel && !$forceCheck)
		{
			return $v;
		}

		$rows = $this->checkboxRows('id');

		foreach ($rows as $r)
		{
			if ($r->value == $v)
			{
				return $r->text;
			}
		}

		return $v;
	}

	/**
	 * Create the sql query used to get the join data
	 *
	 * @param   array  $data      data
	 * @param   bool   $incWhere  include where
	 * @param   array  $opts      query options
	 *
	 * @return  mixed	JDatabaseQuery or false if query can't be built
	 */
	protected function buildQuery($data = array(), $incWhere = true, $opts = array())
	{
		$db = $this->getDb();
		$query = $db->getQuery(true);
		$join = $this->getJoin();
		$query = $this->buildQueryWhere($data, $incWhere, null, $opts, $query);
		$query->select('DISTINCT(t.id) AS value,' . $db->qn('title') . ' AS text')
		->from($db->qn($join->table_join) . ' AS ' . $db->qn($join->table_join_alias))
		->join('LEFT', '#__tags AS t ON t.id = ' . $db->qn($join->table_join_alias . '.' . $join->table_key));

		return $query;
	}

	/**
	 * Called at end of form record save. Used for many-many join elements to save their data
	 *
	 * @param   array  &$data  Form data
	 *
	 * @since  3.1rc1
	 *
	 * @return  void
	 */
	public function onFinalStoreRow(&$data)
	{
		$name = $this->getFullName(true, false);
		$rawName = $name . '_raw';
		$db = FabrikWorker::getDbo(true);
		$formData =& $this->getFormModel()->formDataWithTableName;
		$tagIds = (array) $formData[$rawName];

		foreach ($tagIds as $tagKey => &$tagId)
		{
			if (empty($tagId))
			{
				unset($tagIds[$tagKey]);
				continue;
			}

			// New tag added
			if (strstr($tagId, '#fabrik#'))
			{
				$tagId = $db->quote(str_replace('#fabrik#', '', $tagId));
				$query = $db->getQuery(true);
				$query->insert('#__tags')->set('level = 1, published = 1, parent_id = 1, created_user_id = ' . (int) $this->user->get('id'))
				->set('created_time = ' . $db->q($this->date->toSql()), ', language = "*", version = 1')
				->set('path = ' . $tagId . ', title = ' . $tagId . ', alias = ' . $tagId);
				$db->setQuery($query);
				$db->execute();
				$tagId = $db->insertid();
			}
		}

		$formData[$name] = $tagIds;
		$formData[$rawName] = $tagIds;
		parent::onFinalStoreRow($data);
	}

	/**
	 * Optionally pre-format list data before rendering to <ul>
	 *
	 * @param   array  &$data    Element Data
	 * @param   array  $thisRow  Row data
	 *
	 * @return  void
	 */
	protected function listPreformat(&$data, $thisRow)
	{
		if (empty($data))
		{
			return;
		}

		$name = $this->getFullName(true, false);
		$idName = $name . '_id';

		// isn't set when coming back from submit from AJAX popup form
		if (isset($thisRow->$idName))
		{
			if (is_object($thisRow->$idName))
			{
				$ids = JArrayHelper::fromObject($thisRow->$idName);
			}
			else
			{
				$ids = explode(GROUPSPLITTER, $thisRow->$idName);
			}

			$merged = array_combine($ids, $data);
			$baseUrl = $this->tagUrl();
			$icon = $this->tagIcon();
			$data = FabrikHelperHTML::tagify($merged, $baseUrl, $name, $icon);
		}
	}

	/**
	 * Build the base URL for the tag filter links
	 *
	 * @return string
	 */
	protected function tagUrl()
	{
		$name = $this->getFullName(true, false);
		$rawName = $name . '_raw';
		$baseUrl = FabrikHelperHTML::tagBaseUrl($rawName, $this->tagListURL());
		$baseUrl .= FabrikString::qsSepChar($baseUrl);
		$baseUrl .= $rawName . '={key}';

		return $baseUrl;
	}

	/**
	 * Get the tag icon
	 *
	 * @return string
	 */
	protected function tagIcon()
	{
		$params = $this->getParams();
		$icon = $params->get('tag_icon', '');
		$icon = $icon === '' ? '' : FabrikHelperHTML::icon($icon);

		return $icon;
	}

	/**
	 * Get tag list URL
	 *
	 * @return string
	 */
	protected function tagListURL()
	{
		$listModel = $this->getListModel();

		if ($this->app->isAdmin())
		{
			$url = 'index.php?option=com_fabrik&amp;task=list.view&amp;listid=' . $listModel->getId();
		}
		else
		{
			$url = 'index.php?option=com_' . $this->package . '&amp;view=list&amp;listid=' . $listModel->getId();
			$url = JRoute::_($url);
		}
		return $url;
	}
}
