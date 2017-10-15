<?php 

interface ViewInterface {
	public function render($data, $templateFile);
	public function getHeader();
}
