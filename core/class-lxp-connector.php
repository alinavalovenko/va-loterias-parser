<?php

class Lxp_Connector {
	public $data;

	public function __construct( $api_url ) {
		$this->data = $this->get_entries_by_api( $api_url );
	}

	protected function get_entries_by_api( $api_url ) {
		$content_xml   = file_get_contents( $api_url );
		$lotteries_xml = new SimpleXMLElement( $content_xml );
		$lotteries_arr = json_decode( json_encode( $lotteries_xml ), true );
		$this->convert_antries_to_post_type( $lotteries_arr['entry'] );

		return $lotteries_arr['entry'];
	}

	protected function convert_antries_to_post_type( $entries = null ) {
		foreach ( $entries as $id => $entry ) {
			$lottery_id           = $entry['lottery_id'];
			$title                = $entry['title'];
			$published            = $entry['published'];
			$updated              = $entry['updated'];
			$post_content         = $entry['content'];
			$link_id              = $entry['id'];
			$lottery_logo         = $entry['lottery_logo'];
			$last_draw_date       = $entry['last_draw_date'];
			$last_draw_results    = $entry['last_draw_results'];
			$next_draw_date       = $entry['next_draw_date'];
			$next_draw_jackpot    = $entry['next_draw_jackpot'];
			$next_draw_close_date = $entry['next_draw_close_date'];
			$play_link            = $entry['play_link'];
			$args                 = array(
				'posts_per_page' => - 1,
				'post_type'      => 'entry',
				'meta_query'    => array(
					array(
						'key'       => 'lottery_id',
						'value'     => $lottery_id,
						'compare'   => 'LIKE',
					),
			));
			$post                 = get_posts( $args );
			if ( ! empty( $post ) ) {
				/*update here*/
			} else {
				$post_data = array(
					'post_title'   => wp_strip_all_tags( $title ),
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_date'    => $published,
					'post_type'    => 'entry',
					'meta_input'   => array(
						'lottery_id'           => $lottery_id,
						'link_id'              => $link_id,
						'lottery_logo'         => $lottery_logo,
						'last_draw_date'       => $last_draw_date,
						'last_draw_results'    => $last_draw_results,
						'next_draw_date'       => $next_draw_date,
						'next_draw_jackpot'    => $next_draw_jackpot,
						'next_draw_close_date' => $next_draw_close_date,
						'play_link'            => $play_link
					),
				);

				$post_id = wp_insert_post( $post_data );

			}
		}
	}

}