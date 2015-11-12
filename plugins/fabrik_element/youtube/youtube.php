<?php
/**
 * Render an embedded youtube video play
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.youtube
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Render an embedded youtube video play
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.youtube
 * @since       3.0
 */
class PlgFabrik_ElementYoutube extends PlgFabrik_Element
{
	protected $pluginName = 'youtube';

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      Elements data
	 * @param   stdClass  &$thisRow  All the data in the lists current row
	 * @param   array     $opts      Rendering options
	 *
	 * @return  string	formatted value
	 */
	public function renderListData($data, stdClass &$thisRow, $opts = array())
	{
		return $this->constructVideoPlayer($data, 'list');
	}

	/**
	 * Do we need to include the lightbox js code
	 *
	 * @return  bool
	 */
	public function requiresLightBox()
	{
		return true;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to pre-populate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */
	public function render($data, $repeatCounter = 0)
	{
		$input = $this->app->input;
		$params = $this->getParams();
		$element = $this->getElement();
		$data = $this->getFormModel()->data;
		$value = $this->getValue($data, $repeatCounter);
		$data = array();

		// Stop "'s from breaking the content out of the field.
		$data['value'] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

		if ($input->get('view') != 'details')
		{
			$class = 'fabrikinput inputbox text';
			$name = $this->getHTMLName($repeatCounter);
			$id = $this->getHTMLId($repeatCounter);

			if ($this->elementError != '')
			{
				$class .= " elementErrorHighlight";
			}

			if (!$this->isEditable())
			{
				return ($element->hidden == '1') ? '<!-- ' . $value . ' -->' : $value;
			}

			$layout = $this->getLayout('form');
			$layoutData = new stdClass;
			$layoutData->id = $id;
			$layoutData->name = $name;
			$layoutData->class = $class;;
			$layoutData->size = $params->get('width');
			$layoutData->maxlength = 255;

			return $layout->render($layoutData);
		}
		else
		{
			return $this->constructVideoPlayer($value);
		}
	}

	/**
	 * Make video player
	 *
	 * @param   string  $value  Value
	 * @param   string  $mode   Mode form/list
	 *
	 * @return string
	 */
	private function constructVideoPlayer($value, $mode = 'form')
	{
		$params = $this->getParams();

		// Player size
		if (($params->get('display_in_table') == 0) && $mode == 'list')
		{
			$width = '170';
			$height = '142';
		}
		else
		{
			if ($params->get('or_width_player') != null)
			{
				$width = $params->get('or_width_player');
				$height = $params->get('or_height_player');
			}
			else
			{
				if ($params->get('player_size') == 'small')
				{
					$width = '340';
					$height = '285';
				}
				elseif ($params->get('player_size') == 'medium')
				{
					$width = '445';
					$height = '364';
				}
				elseif ($params->get('player_size') == 'normal')
				{
					$width = '500';
					$height = '405';
				}
				else
				{
					$width = '660';
					$height = '525';
				}
			}
		}
		// Include related videos
		$rel = $params->get('include_related') == 0 ? '&rel=0' : '';

		// Enable delayed cookies
		$url = $params->get('enable_delayed_cookies') == 1 ? 'http://www.youtube-nocookie.com/v/' : 'http://www.youtube.com/v/';

		$vid_array = explode("/", $value);
		$vid = array_pop($vid_array);

		// If one copies an URL from youtube, the URL has the "watch?v=" which barfs the player
		if (strstr($vid, 'watch'))
		{
			$vid = explode('=', $vid);

			// That's the watch?v=
			unset($vid[0]);
			$vid = implode('', $vid);
		}

		if ($vid == '')
		{
			// $$$ rob perhaps they just added in the code???
			$vid = $value;
		}

		if ($value != null)
		{
			if ($params->get('display_in_table') == 1 && $mode == 'list')
			{
				// Display link
				if ($params->get('display_link') == 0)
				{
					$object_vid = $value;
				}
				else
				{
					if ($params->get('display_link') == 1)
					{
						$dlink = $value;
					}
					else
					{
						$dlink = $params->get('text_link') != null ? $params->get('text_link') : 'Watch Video';
					}

					$element = $this->getElement();
					$layoutData = new stdClass;
					$layoutData->link = $params->get('target_link');
					$layoutData->value = $url . $vid;
					$layoutData->width = $width;
					$layoutData->height = $height;
					$layoutData->title = $element->label;
					$layoutData->label = $dlink;
					$layout = $this->getLayout('list');

					return $layout->render($layoutData);
				}
			}
			else
			{
				$layout = $this->getLayout('detail');
				$layoutData = new stdClass;
				$layoutData->width = $width;
				$layoutData->height = $height;
				$layoutData->value = $url . $vid . '&hl=en&fs=1' . $rel;

				return $layout->render($layoutData);
			}
		}
		else
		{
			$object_vid = '';
		}

		return $object_vid;
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

		return array('FbYouTube', $id, $opts);
	}

	/**
	 * Get database field description
	 *
	 * @return  string  db field type
	 */
	public function getFieldDescription()
	{
		$p = $this->getParams();

		if ($this->encryptMe())
		{
			return 'BLOB';
		}

		$objType = 'VARCHAR(' . $p->get('maxlength', 255) . ')';

		return $objType;
	}
}
