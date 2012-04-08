<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * FancyCheckboxWizard
 * Copyright (C) 2010,2011 InfinitySoft <http://www.infinitysoft.de>
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  2010,2011 InfinitySoft <http://www.infinitysoft.de>
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    FancyCheckboxWizard
 * @license    LGPL
 */


/**
 * Class FancyCheckBoxWizard
 *
 * Provide methods to handle sortable checkboxes.
 * @copyright  Tristan Lins 2011
 * @copyright  Leo Feyer 2005-2011
 * @author     Tristan Lins <http://www.infinitysoft.de>
 * @author     John Brand <http://www.thyon.com>
 * @author     Leo Feyer <http://www.contao.org>
 * @package    FancyCheckboxWizard
 */
class FancyCheckBoxWizard extends CheckBoxWizard
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget_fancy_checkbox_wizard';

	/**
	 * Values of checked options.
	 *
	 * @var array
	 */
	protected $arrCheckedOptions = array();

	/**
	 * Values of disabled options.
	 *
	 * @var array
	 */
	protected $arrDisabledOptions = array();

	/**
	 * Mixin options.
	 *
	 * @var array
	 */
	protected $arrMixinValue = array();

	/**
	 * Initialize the object
	 *
	 * @param array
	 *
	 * @throws Exception
	 */
	public function __construct($arrAttributes = false)
	{
		parent::__construct($arrAttributes);

		// Checked options
		if (isset($arrAttributes['checked_options_callback']) && is_array($arrAttributes['checked_options_callback'])) {
			$callback = $arrAttributes['checked_options_callback'];
			$this->import($callback[0]);
			$this->arrCheckedOptions = $this->$callback[0]->$callback[1]($this);
		}
		else if (isset($arrAttributes['checked_options']) && is_array($arrAttributes['checked_options'])) {
			$this->arrCheckedOptions = $arrAttributes['checked_options'];
		}

		// Disabled options
		if (isset($arrAttributes['disabled_options_callback']) && is_array($arrAttributes['disabled_options_callback'])) {
			$callback = $arrAttributes['disabled_options_callback'];
			$this->import($callback[0]);
			$this->arrDisabledOptions = $this->$callback[0]->$callback[1]($this);
		}
		else if (isset($arrAttributes['disabled_options']) && is_array($arrAttributes['disabled_options'])) {
			$this->arrDisabledOptions = $arrAttributes['disabled_options'];
		}

		// Mixin value
		if (isset($arrAttributes['mixin_value_callback']) && is_array($arrAttributes['mixin_value_callback'])) {
			$callback = $arrAttributes['mixin_value_callback'];
			$this->import($callback[0]);
			$this->arrMixinValue = $this->$callback[0]->$callback[1]($this);
		}
		else if (isset($arrAttributes['mixin_value']) && is_array($arrAttributes['mixin_value'])) {
			$this->arrMixinValue = $arrAttributes['mixin_value'];
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_CSS']['fancyCheckboxWizard'] = 'system/modules/fancyCheckboxWizard/html/backend.css';

		$arrOptions                = array();
		$arrDisabledCheckedOptions = array();
		$arrCheckedOptions         = array();
		$arrUncheckedOptions       = array();

		if (!is_array($this->varValue)) {
			$this->varValue = array($this->varValue);
		}

		if (is_array($this->arrMixinValue)) {
			$this->varValue = array_merge($this->arrMixinValue, $this->varValue);
		}

		foreach ($this->arrOptions as $arrOption)
		{
			$arrOption['checked'] = in_array($arrOption['value'], $this->arrCheckedOptions) || in_array($arrOption['value'], $this->varValue);
			$arrOption['disabled'] = in_array($arrOption['value'], $this->arrDisabledOptions);
			$arrOptions[$arrOption['value']] = $arrOption;
		}

		// sorted values
		$arrSort = array_keys($arrOptions);

		// sort options by selected state and custom ordering
		if ($this->varValue) {
			// Move selected and sorted options to the top
			foreach ($arrOptions as $arrOption)
			{
				if (($intPos = array_search($arrOption['value'], $this->varValue)) !== false) {
					if ($arrOption['disabled']) {
						$arrDisabledCheckedOptions[$intPos] = $arrOption;
					}
					else
					{
						$arrCheckedOptions[$intPos] = $arrOption;
					}
				}
				else
				{
					$arrUncheckedOptions[] = $arrOption;
				}
			}
		}

		// sort by position
		ksort($arrDisabledCheckedOptions);
		ksort($arrCheckedOptions);

		$n = 1;
		// generate the options
		foreach ($arrDisabledCheckedOptions as $i => $arrOption)
		{
			$arrDisabledCheckedOptions[$i] = $this->generateFancyCheckbox($arrOption, $n++);
		}
		foreach ($arrCheckedOptions as $i => $arrOption)
		{
			$arrCheckedOptions[$i] = $this->generateFancyCheckbox($arrOption, $n++);
		}
		foreach ($arrUncheckedOptions as $i => $arrOption)
		{
			$arrUncheckedOptions[$i] = $this->generateFancyCheckbox($arrOption, $n++);
		}

		return sprintf('<fieldset id="ctrl_%s" class="tl_checkbox_container tl_fanzy_checkbox_wizard%s"><legend>%s%s%s%s</legend>%s<ul id="ctrl_%s_checked" class="sortable">%s</ul><hr><ul id="ctrl_%s_unchecked" class="sortable">%s</ul></fieldset>%s%s',
			$this->strId,
			(($this->strClass != '') ? ' ' . $this->strClass : ''),
			($this->required ? '<span class="invisible">' . $GLOBALS['TL_LANG']['MSC']['mandatory'] . '</span> ' : ''),
			$this->strLabel,
			($this->required ? '<span class="mandatory">*</span>' : ''),
			$this->xlabel,
			count($arrDisabledCheckedOptions) ? sprintf('<ul id="ctrl_%s_disabled_checked" class="sortable">%s</ul>', $this->strId, implode('', $arrDisabledCheckedOptions)) : '',
			$this->strId,
			count($arrOptions) ? implode('', $arrCheckedOptions) : '<p class="tl_noopt">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>',
			$this->strId,
			count($arrOptions) ? implode('', $arrUncheckedOptions) : '',
			$this->wizard,
			'<script>
(function() {
	var sorted = ' . json_encode($arrSort) . ';
	var sortable = new Sortables("ctrl_' . $this->strId . '_checked", { handle: "img.cut" });
	$$("#ctrl_' . $this->strId . ' ul li input[type=\'checkbox\']").each(function(e) {
		e.addEvent("change", function() {
			var li = $(this).getParent("li");
			if (this.checked) {
				li.getElement("img.cut").setStyle("display", "");
				li.getElement("img.cut_").setStyle("display", "none");
				li.inject("ctrl_' . $this->strId . '_checked");
				sortable.addItems(li);
			} else {
				li.getElement("img.cut").setStyle("display", "none");
				li.getElement("img.cut_").setStyle("display", "");
				sortable.removeItems(li);
				var items = $$("ul#ctrl_' . $this->strId . '_unchecked li input[type=\'checkbox\']");
				for (var i = sorted.indexOf(e.value.test(/^\d+/) ? parseInt(e.value) : e.value)+1; i<sorted.length; i++) {
					var item = $$("ul#ctrl_' . $this->strId . '_unchecked li input[value=\'" + sorted[i] + "\']");
					if (item.length) {
						li.inject(item[0].getParent("li"), "before");
						return;
					}
				}
				li.inject("ctrl_' . $this->strId . '_unchecked");
			}
		});
	});
})();
</script>');
	}


	/**
	 * Generate a checkbox and return it as string
	 *
	 * @param array
	 * @param integer
	 * @param string
	 *
	 * @return string
	 */
	protected function generateFancyCheckbox($arrOption, $i)
	{
		return sprintf('<li style="cursor:auto;">%s%s&nbsp;<input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s%s onfocus="Backend.getScrollOffset();"> <label for="opt_%s">%s</label></li>',
			$this->generateImage('cut_.gif', '', 'class="cut_" style="vertical-align: middle;' . ($arrOption['checked'] && !$arrOption['disabled'] ? ' display:none;' : '') . '"'),
			$this->generateImage('cut.gif', '', 'class="cut" style="vertical-align: middle; cursor: move;' . ($arrOption['checked'] && !$arrOption['disabled'] ? '' : ' display:none;') . '"'),
			$this->strName . ($this->multiple ? '[]' : ''),
			$this->strId . '_' . $i,
			($this->multiple ? specialchars($arrOption['value']) : 1),
			($arrOption['checked'] ? ' checked="checked"' : ''),
			$this->getAttributes(),
			($arrOption['disabled'] ? ' disabled="disabled"' : ''),
			$this->strId . '_' . $i,
			$arrOption['label']);
	}
}
