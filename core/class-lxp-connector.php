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
		$status = $this->convert_entries_to_post_type( $lotteries_arr['entry'] );

		return $status;
	}

	protected function convert_entries_to_post_type( $entries = null ) {
		$status = 'error';
		foreach ( $entries as $id => $entry ) {
			$lottery_id           = empty($entry['lottery_id']) ? '': $entry['lottery_id'];
			$title                = empty($entry['title']) ? '': $entry['title'];
			$published            = empty($entry['published']) ? '': $entry['published'];
			$updated              = empty($entry['updated']) ? '': $entry['updated'];
			$post_content         = empty($entry['content']) ? '': $entry['content'];
			$link_id              = empty($entry['id']) ? '': $entry['id'];
			$lottery_logo         = empty($entry['lottery_logo']) ? '': $entry['lottery_logo'];
			$last_draw_date       = empty($entry['last_draw_date']) ? '': $entry['last_draw_date'];
			$last_draw_results    = empty($entry['last_draw_results']) ? '': $entry['last_draw_results'];
			$next_draw_date       = empty($entry['next_draw_date']) ? '': $entry['next_draw_date'];
			$next_draw_jackpot    = empty($entry['next_draw_jackpot']) ? '': $entry['next_draw_jackpot'];
			$next_draw_close_date = empty($entry['next_draw_close_date']) ? '': $entry['next_draw_close_date'];
			$play_link            = empty($entry['play_link']) ? '': $entry['play_link'];
			$args                 = array(
				'posts_per_page' => 1,
				'post_type'      => 'lottery',
				'meta_key'       => 'lottery_id',
				'meta_value'     => $lottery_id
			);
			$post                 = get_posts( $args );
			if ( ! empty( $post ) ) {
				$post_data = array(
					'ID'           => $post[0]->ID,
					'post_title'   => wp_strip_all_tags( $title ),
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_date'    => $published,
					'post_type'    => 'lottery',
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
				wp_update_post($post_data);
				$this->updated_fetured_image($post[0]->ID, $lottery_logo);
			} else {
				$post_data = array(
					'post_title'   => wp_strip_all_tags( $title ),
					'post_content' => $post_content,
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_date'    => $published,
					'post_type'    => 'lottery',
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
				$post_id   = wp_insert_post( $post_data );
				$this->set_featured_image( $post_id, $lottery_logo, $title );
			}
		}
		$status = 'success';
		return $status;
	}

	protected function set_featured_image( $post_id, $image_url = null, $image_name = 'undefined' ) {
		$image_size       = getimagesize( $image_url );
		$image_format     = explode( '/', $image_size['mime'] )[1];
		$image_name       = $image_name . '.' . $image_format;
		$upload_dir       = wp_upload_dir();
		$image_data       = file_get_contents( $image_url );
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
		$filename         = basename( $unique_file_name );

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		file_put_contents( $file, $image_data );

		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		wp_update_attachment_metadata( $attach_id, $attach_data );

		set_post_thumbnail( $post_id, $attach_id );
	}

	protected function updated_fetured_image( $post_id, $image_url ) {
		$old_image_url = get_post_meta( $post_id, 'lottery_logo', true );
		if ( $image_url == $old_image_url ) {
			return;
		} else {
			$post_title = get_the_title( $post_id );
			$this->set_featured_image( $post_id, $image_url, $post_title );
		}
	}

}