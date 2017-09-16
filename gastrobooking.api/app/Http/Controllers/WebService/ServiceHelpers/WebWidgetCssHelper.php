<?php
namespace App\Http\Controllers\WebService\ServiceHelpers;

use App;

class WebWidgetCssHelper {
    private $selectors = [];
    private $styles = [];
    private $styles_string = null;

    /**
     * Function for adding parameters into `CSSGenerator` class
     *
     * @param $selectors Array - Parameters for adding to `$this->selectors` variable
     * @return $this
     */
    public function setSelectors($selectors) {
        $this->selectors = array_merge($this->selectors, $selectors);

        return $this;
    }

    /**
     * Function for converting array with parameters into css array
     *
     * @param $params Array - Parameters with elements and his css selectors
     * @return $this
     */
    public function generate($params) {
        $styles = [];

        foreach($params as $key_parameter => $parameters) {
            if(in_array($key_parameter, array_keys($this->selectors))) {
                $selector = $this->selectors[$key_parameter];

                // when selector with parameter
                if(is_array($this->selectors[$key_parameter])) {
                    $parameter = $parameters;

                    $options = isset($selector[1]) ? $selector[1] : null;
                    if($options && is_array($options) && isset($options[$parameter])) {
                        $sub_parameters = $options[$parameter];
                    } else {
                        $sub_parameters =[$selector[1] => $parameter];
                    }

                    $styles[] = $this->generateSelectors($key_parameter, $selector[0], $sub_parameters);
                }
                // when selector without parameter
                else {
                    $styles[] = $this->generateSelectors($key_parameter, $selector, $parameters);
                }
            }
        }

        $this->styles = $styles;

        return $this;
    }

    /**
     * Function for generate CSS selector an array
     *
     * @param $key_parameter - Key name for call need selector from `$this->selectors`
     * @param $selector - Css selector
     * @param $parameters - Parameters current `$selector`
     * @return mixed Array - Array with elements of selector
     */
    private function generateSelectors($key_parameter, $selector, $parameters) {
        $styles[$key_parameter][] = "{$selector} {";
        foreach($parameters as $key => $parameter) {
            $styles[$key_parameter][] = "{$key}: {$parameter} !important;";
        }
        $styles[$key_parameter][] = "}";

        return $styles;
    }

    /**
     * Function for converting CSS array with selectors in string
     *
     * @return $this
     */
    public function getSelectors() {
        $styles = null;
        foreach ($this->styles as $type_styles) {
            foreach ($type_styles as $selectors) {
                foreach ($selectors as $key => $selector) {
                    // Added tab for all attributes in selector
                    $tab = !($key == 0 || $key == count($selectors) - 1) ? "\t" : null;
                    $styles .= $tab. $selector . "\n";
                }
                $styles .= "\n";
            }
        }

        $this->styles_string = $styles;

        return $this;
    }

    /**
     * Function that adding tags for styles
     *
     * @return string
     */
    public function getCSS() {
        $styles_string = $this->styles_string;
        return "<style type=\"text/css\">{$styles_string}</style>";
    }

    /**
     * Function for getting css selectors an string
     *
     * @return string
     */
    public function get() {
        return $this->styles_string;
    }
}

class JSGenerator {
    private $scripts = [];
    private $scripts_string = null;

    /**
     * Function for adding scripts into `JSGenerator` class
     *
     * @param $selectors Array - Parameters for adding to `$this->selectors` variable
     * @return $this
     */
    public function setScripts($scripts) {
        $this->scripts = array_merge($this->scripts, $scripts);

        return $this;
    }

    /**
     * Function for converting array with scripts into scripts string
     *
     * @param $params Array - Parameters with elements and his css selectors
     * @return $this
     */
    public function generate($params) {
        foreach($params as $key => $val) {
            if(in_array($key, array_keys($this->scripts))) {
                $this->scripts_string .= $this->scripts[$key];

                // Add to script variable
                $this->scripts_string = str_replace("@{" . $key . "}", $val, $this->scripts_string);
            }
        }

        // Add init function jQuery
        $this->scripts_string= "$(function(){ setTimeout(function() {" . $this->scripts_string . "}, 100); });";

        return $this;
    }

    /**
     * Function for getting js scripts an string
     *
     * @return string
     */
    public function get() {
        return $this->scripts_string;
    }
}
