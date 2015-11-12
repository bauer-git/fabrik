<?php
/**
 * Layout: Yes/No field list view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.2
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$d           = $displayData;
$baseContext = $d->baseContext;
$app         = JFactory::getApplication();

?>
<div class="radius_search" id="radius_search<?php echo $d->renderOrder; ?>" style="left:-100000px;position:absolute;">

	<input type="hidden" name="radius_search_active<?php echo $d->renderOrder; ?>[]" value="<?php echo $d->active; ?>" />

	<div class="radius_search_options">

		<input type="hidden" name="geo_code_def_zoom" value="<?php echo $d->defaultZoom; ?>" />
		<input type="hidden" name="geo_code_def_lat" value="<?php echo $d->defaultLat; ?>" />
		<input type="hidden" name="geo_code_def_lon" value="<?php echo $d->defaultLon; ?>" />

		<div class="row-fluid">
			<div class="span4">
				<?php echo FText::_('PLG_VIEW_RADIUS_DISTANCE'); ?>
			</div>
			<div class="span8">
				<div class="slider_cont" style="width:200px;">
					<div class="fabrikslider-line" style="width:200px">
						<div class="knob"></div>
					</div>
					<input type="hidden" class="radius_search_distance" name="radius_search_distance<?php echo $d->renderOrder; ?>" value="<?php echo $d->distance; ?>" />

					<div class="slider_output">"<?php echo $d->distance . ' ' . $d->unit; ?></div>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<label for="radius_search_type0"><?php echo FText::_('PLG_VIEW_RADIUS_FROM'); ?></label>
			</div>
			<div class="span8">
				<?php echo $d->select; ?>
			</div>
		</div>
		<div class="radius_table fabrikList table" style="width:100%">
			<?php
			$style   = $d->type == 'place' ? 'display:block' : 'display:none';
			$context = $baseContext . 'radius_search_place-auto-complete';
			$name    = "radius_search_place{$d->renderOrder}-auto-complete";
			$place   = $app->getUserStateFromRequest($context, $name);
			?>
			<div class="radius_search_place_container" style="<?php echo $style; ?>;position:relative;">
				<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="inputbox fabrik_filter autocomplete-trigger" value="<?php echo $place; ?>" />

				<?php
				$context    = $baseContext . 'radius_search_place';
				$name       = 'radius_search_place' . $d->renderOrder;
				$placeValue = $app->getUserStateFromRequest($context, $name); ?>

				<input type="hidden" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="inputbox fabrik_filter autocomplete-trigger" value="<?php echo $placeValue; ?>" />
			</div>

			<?php $style = $d->type == 'latlon' ? 'display:block' : 'display:none'; ?>

			<div class="radius_search_coords_container" style="<?php echo $style; ?>">

				<div class="row-fluid">
					<div class="span4">
						<label for="radius_search_lat_<?php echo $d->renderOrder; ?>"><?php echo FText::_('PLG_VIEW_RADIUS_LATITUDE'); ?>
						</label>
					</div>
					<div class="span8">
						<input type="text" name="radius_search_lat<?php echo $d->renderOrder; ?>" value="<?php echo $d->lat; ?>" id="radius_search_lat_<?php echo $d->renderOrder; ?>" size="6" class="inputbox fabrik_filter autocomplete-trigger" />
					</div>
				</div>
				<div class="row-fluid">
					<div class="span4">
						<label for="radius_search_lon_<?php echo $d->renderOrder; ?>"><?php echo FText::_('PLG_VIEW_RADIUS_LONGITUDE'); ?>
						</label>
					</div>
					<div class="span8">
						<input type="text" name="radius_search_lon<?php echo $d->renderOrder; ?>" value="<?php echo $d->lon; ?>" id="radius_search_lon_<?php echo $d->renderOrder; ?>" size="6" class="inputbox fabrik_filter autocomplete-trigger" />
					</div>
				</div>

			</div>

			<?php

			$style = $d->hasGeocode && $d->type == 'geocode' ? '' : 'position:absolute;left:-10000000px;';

			?>
			<div class="radius_search_geocode input-append" style="<?php echo $style; ?>">
				<input type="text" class="radius_search_geocode_field"
					name="radius_search_geocode_field<?php echo $d->renderOrder; ?>" value="<?php echo $d->address; ?>" />
				<?php
				if (!$d->geocodeAsYouType) :
					?>
					<button class="btn button"><?php echo FText::_('COM_FABRIK_SEARCH'); ?></button>
					<?php
				endif;
				?>

				<div class="radius_search_geocode_map" id="radius_search_geocode_map<?php echo $d->renderOrder; ?>"></div>
				<input type="hidden" name="radius_search_geocode_lat<?php echo $d->renderOrder; ?>" value="<?php echo $d->searchLatitude; ?>" />
				<input type="hidden" name="radius_search_geocode_lon<?php echo $d->renderOrder; ?>" value="<?php echo $d->searchLongitude; ?>" />
			</div>
			<div class="radius_search_buttons" id="radius_search_buttons<?php echo $d->renderOrder; ?>">
				<input type="button" class="btn btn-link cancel" value="<?php echo FText::_('COM_FABRIK_CANCEL'); ?>" />
				<input type="button" name="filter" value="Go" class="fabrik_filter_submit button btn btn-primary"></div>
		</div>
	</div>

	<input type="hidden" name="radius_prefilter" value="1" />

</div>