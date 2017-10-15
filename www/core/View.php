<?php
#################################
#       GydruS's Engine 3       #
#          "View" class         #
#             v. 1.0            #
#           2012 10 10          #
#################################

#################################
# Description
#--------------------------------
#
#

class View
{
    private $engine;
    private $templateAdapter;
 
    public function __construct($engine, $errorHandler = null) {
		$this->engine = $engine;
		switch ($engine) {
			case 'xml' : Load::viewAdapter('XMLAdapter'); $this->templateAdapter = new XMLAdapter($errorHandler); break;
			case 'xslt' : Load::viewAdapter('XSLTAdapter'); $this->templateAdapter = new XSLTAdapter($errorHandler); break;
			case 'php' : Load::viewAdapter('PHPAdapter'); $this->templateAdapter = new PHPAdapter($errorHandler); break;
			case 'raw' : Load::viewAdapter('RawAdapter'); $this->templateAdapter = new RawAdapter($errorHandler); break;
			case 'json' : Load::viewAdapter('JSONAdapter'); $this->templateAdapter = new JSONAdapter($errorHandler); break;
 		}
	}
	
    public function render($data, $template) {
		return $this->templateAdapter->render($data, $template);
    }

    public function getHeader() {
		return $this->templateAdapter->getHeader();
    }

}
