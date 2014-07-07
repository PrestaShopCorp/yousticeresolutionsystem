<?php

/**
 * Youstice show buttons widget.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice\Widgets;

/**
 * Description of ShowButtonsWidget
 *
 */
class ShowButtonsWidget {

    protected $href;
    protected $hasReports;
    protected $translator;

    public function __construct($lang, $hasReports) {
        $this->hasReports = $hasReports;
        $this->translator = new \Youstice\Translator($lang);
    }

    public function toString() {
        //todo translate if final
        $text = $this->translator->t('Would you like to file a complaint?');
        
        //todo debug
        //$this->hasReports = 0;
        
        return '<a class="yrsShowButtons yrsButton" '
            . 'data-has-reports="'.(boolean)$this->hasReports.'">'
            . $text . '</a>';
    }

}
