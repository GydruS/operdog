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
class XSLTAdapter extends BaseViewAdapter // implements ViewInterface
{
    public function render($data, $templateFile) {
		if(!is_array($data)) $data = Array($data);
                
                array_walk_recursive($data, array(&$this, 'filter'));
        
        if (!empty($this->errorHandler)) libxml_use_internal_errors(true);
		
		# Формируем XML-строку для ее скрещивания с XSL
		#   2ДУ: Сделать все это без строки XML, а в работе непосредственно с одним экземпляром
		#   DOMDocument'а.
		$dom = new DOMDocument();
		$converter = new Array2XML();
		$xmlStr = $converter->convert($data);
        //print $xmlStr;//var_dump($xmlStr);
		$dom->loadXML($xmlStr);		
		//ge_VarToDom($dom, $dom, 'document', $ge_Result);
		
		# Загружаем XSL-шаблон, выполняем преобразвание и отдаем клиенту результат
		$doc = new DOMDocument();
		$xsl = new XSLTProcessor();
		if(!$doc->load($templateFile))
		{
            $this->handleError('Error while loading "'.$templateFile.'" template!');
			#Unable to load template
			//$templateName = 'error';
			//$doc->load($templatesPath.'/'.$templateName.'.'.$templatesExtension);
		}
		$xsl->importStyleSheet($doc);
		
		#ob_start();
		//echo $xsl->transformToXML($dom);
		
        $res = $xsl->transformToXML($dom);
        
        if (!empty($this->errorHandler)) {
            $errors = libxml_get_errors();
            foreach ($errors as $key => $error) {
                $errorMessage = $error->message;
                $errorCode = $error->code;
                $errorDescription = 'level: '.$error->level.'; ';
                $errorDescription .= 'file: '.$error->file.'; ';
                $errorDescription .= 'line: '.$error->line.'; ';
                $errorDescription .= 'column: '.$error->column.'; ';
                $time = null;
                $this->handleError($errorMessage, $errorCode, $errorDescription, $time);
            }
            libxml_use_internal_errors(false);
        }
        
		return $res;
    }
    
    public function xsltErrorHandler($handler, $errno, $level, $info)
    {
        // проверим список аргументов
        var_dump(func_get_args());
    }
	
    public function filter(&$value) {
        $value = str_replace("&nbsp;", " ", $value);
    }
}
