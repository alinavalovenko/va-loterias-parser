<?php

class Lxp_Connector {
	public $data;

	public function __construct( $api_url ) {
		$this->data = $this->get_xml_by_url( $api_url );
	}

	protected function get_xml_by_url( $api_url ) {
		$content_xml = file_get_contents( $api_url );
		return $content_xml;
	}
}