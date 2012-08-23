<?php
 /**
  * Template engine
  * @author Gouverneur Thomas <tgo@ians.be>
  * @copyright Copyright (c) 2007-2008, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage template
  * @category classes
  * @filesource
  */

class Template {

    private $vars = array();

    /**
     * Constructor
     *
     * @param $file string the file name you want to load
     */
    function Template($file = null) {
        $this->file = $file;
    }

    /**
     * Set a template variable.
     */
    function set($name, $value) {
        if (is_object($value) && get_class($value) == "Template") {
          $this->vars[$name] = $value->fetch();
        } else {
          $this->vars[$name] = $value;
        }
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param $file string the template file name
     */
    function fetch($file = null) {
        if(!$file) $file = $this->file;

        extract($this->vars);          // Extract the vars to local namespace
        ob_start();                    // Start output buffering
	if (file_exists($file))
          include($file);                // Include the file
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        return $contents;              // Return the contents
    }

 
    /**
     * Parse array into template
     */
    function parseArray($ar) {

      $keywords = array("href", "label", "img");

      if (is_array($ar)) {
        $ks = array_keys($ar);
	if (!in_array($ks[0], $keywords, true)) { /* need to parse array hereunder */
	  foreach ($ar as $sar) {
            $this->parseArray($sar);
	  }
	} else { /* array contains token use it */
	  if (isset($ar["href"])) {
            echo "<a href=\"".$ar["href"]."\">";
          }
          if (isset($ar["img"])) {
            echo "<img border=\"0\" src=\"".$ar["img"]."\" alt=\"".$ar["label"]."\"/>";
          } else if (isset($ar["label"])) {
  	    echo $ar["label"];
          }
          if (isset($ar["href"])) {
            echo "</a>";
          }  
	} 
      } else { /* just print the value */
        echo $ar;
      }
    }
}

?>
