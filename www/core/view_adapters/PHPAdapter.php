<?php
#################################
#       GydruS's Engine 3       #
#      "phpAdapter" class       #
#             v. 1.0            #
#	       2012 10 10           #
#################################

#################################
# Description
#--------------------------------
#
#

class PHPAdapter extends BaseViewAdapter // implements ViewInterface
{
    public function render($data, $templateFile) {
        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
				//TODO: Namespace!!!
                $$key = $data[$key];
            }
        }
        ob_start();
        if(file_exists($templateFile))
        {
            include $templateFile;
            $buffer = ob_get_contents();
        }
        else $buffer = false;
        ob_end_clean();
        return $buffer;		
    }
	
}
