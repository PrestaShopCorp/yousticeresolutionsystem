<?php
/**
 * Youstice logo widget.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice\Widgets;

use Youstice\Helpers\HelperFunctions;

/**
 * Description of LogoWidget
 *
 */
class LogoWidget {

	protected $href;
	protected $changedReportStatusesCount;
	protected $translator;
	protected $sh;

	protected $data = array(
		"body" => "",	//info text
		"stats" => "",	//98% resolved cases
		"link" => "",	// anchor's text
		"image" => "",	//base_64 image data
		"heading" => "",	//h5
		"changedReportStatusesCount" => 0,
	);

	public function __construct($href, $lang, $data, $changedReportStatusesCount) {
		$this->data = $data;
		$this->href = $href;
		$this->changedReportStatusesCount = $changedReportStatusesCount;
		$this->translator = new \Youstice\Translator($lang);
	}

	public function toString() {
		$alwaysShowClass = '';
		if($this->changedReportStatusesCount)
			$alwaysShowClass = ' class="alwaysShow"';

		$output =
			'<div id="yousticeLogoWidget"' . $alwaysShowClass . '>
				<a id="yousticeLogo">Youstice</a>
				<div class="content">
					<a href="#" id="logoWidgetClose">&nbsp;</a>';

		if($this->changedReportStatusesCount) {
			$statusesCountText = $this->translator->t('updates of your cases');
			$output .=
					'<div class="newUpdatesText">'
					. '<strong>' . $this->changedReportStatusesCount . '</strong> ' . $statusesCountText . '
					</div>';
		}

		$output .=
					'<h5><img src="' . $this->data['image'] . '">' . HelperFunctions::sh($this->data['heading']) . '</h5>'
				.	'<p>' . $this->data['body'] . '</p>'
				.	'<div class="bottom">';

		//split "98% of resolved..." by percentage
		$percentageString = substr($this->data['stats'], 0, strpos($this->data['stats'], "%") + 1);
		$remainingString = substr($this->data['stats'], strpos($this->data['stats'], "%") + 1);

		$output .=	'<div class="numResolved"><strong>' . HelperFunctions::sh($percentageString).'</strong>'
						. HelperFunctions::sh($remainingString) . '</div>'
				. '		<a href="' . $this->href . '">' . HelperFunctions::sh($this->data['link']) . '</a>'
				. '</div>';

		$output .=
				'</div>
			</div>

		<script type="text/javascript">
			if(IsYousticeLogoVisible() == 0) {
				hideYousticeLogoWidget(false);
			}

			document.getElementById("logoWidgetClose").onclick = function() {
				hideYousticeLogoWidget(true);
				return false;
			};

			document.getElementById("yousticeLogo").onclick = function() {
				var widget = document.getElementById("logoWidgetClose").parentNode.parentNode;

				widget.className = widget.className.replace("logoHidden", "");
				document.cookie = "yousticeShowLogoWidget=1; path=/";
				return false;
			}

			function hideYousticeLogoWidget(byUser) {
				var widget = document.getElementById("logoWidgetClose").parentNode.parentNode;

				if(byUser){
					widget.className = widget.className.replace("alwaysShow", "");
				}

				widget.className = widget.className.replace("logoHidden", "");
				widget.className = widget.className + " logoHidden";
				document.cookie = "yousticeShowLogoWidget=0; path=/";
			}

			function IsYousticeLogoVisible() {
				var name = "yousticeShowLogoWidget=";
				var parts = document.cookie.split(";");
				for(var i=0; i<parts.length; i++) {
					var c = parts[i].trim();
					if (c.indexOf(name)==0)
						return c.substring(name.length,c.length);
				}
				return 1;
			}
		</script>';

		return $output;
	}

}
