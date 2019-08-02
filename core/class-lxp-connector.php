<?php

class Lxp_Connector {

	public $data;

	public function __construct( $api_url ) {
		$this->data = $this->get_entries_by_api( $api_url );
	}

	protected function get_entries_by_api( $api_url ) {
		// grab data by url
		$content_xml   = file_get_contents( $api_url );
		$lotteries_xml = new SimpleXMLElement( $content_xml );
		//convert xml to array
		$lotteries_arr = json_decode( json_encode( $lotteries_xml ), true );
/* dror - 29-6-2019 - use the sorted entries (sorted by next_draw_date)
		$status = $this->convert_entries_to_post_type( $lotteries_arr['entry'] );
*/		
		$sorted_entries = $lotteries_arr['entry'];
		usort($sorted_entries, "compare_entries_by_next_draw_date");
		$status = $this->convert_entries_to_post_type( $sorted_entries );
		return $status;
	}
	
	/**
	 * (dror - 29-6-2019)
	 * compares two entries by their 'next_draw_date time strings,
	 * which are expected to be in the format "29/06/2019 19:30 GMT"
	 */
	protected function compare_entries_by_next_draw_date( $a, $b ) {
		$a_timestamp = strtotime(str_replace('/', '-', $a['next_draw_date']));
		$b_timestamp = strtotime(str_replace('/', '-', $b['next_draw_date']));
		return strcmp($a_timestamp, $b_timestamp);
	}

	/**
	 * Convert entries from xml to 'lottery' post types
	 *
	 * @param null $entries
	 *
	 * @return string 'success' if import was successful
	 */
	protected function convert_entries_to_post_type( $entries = null ) {
		$status = 'error';
		$this->remove_old_data();
		foreach ( $entries as $id => $entry ) {
			// set up variables with new value
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
			// check if we alredy have similar offer.
			if ( ! empty( $post ) ) {
				//Lottery will be update with new values
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
				//create new lottery item
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
				//import lottery logo and set up it as featured image
				$this->set_featured_image( $post_id, $lottery_logo, $title );
			}
		}
		$status = 'success';

		return $status;
	}

	/**
	 * Import logo image and set up it as post featured image
	 *
	 * @param $post_id
	 * @param null $image_url
	 * @param string $image_name
	 */
	protected function set_featured_image( $post_id, $image_url = null, $image_name = 'undefined' ) {
		$image_size       = getimagesize( $image_url );
		$image_format     = explode( '/', $image_size['mime'] )[1]; // define image format
		$image_name       = $image_name . '.' . $image_format; // create correct image name
		$upload_dir       = wp_upload_dir();
		$image_data       = file_get_contents( $image_url );
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
		$filename         = basename( $unique_file_name );

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		file_put_contents( $file, $image_data ); // paste image to upload folder

		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		// save image meta into data base
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		wp_update_attachment_metadata( $attach_id, $attach_data );

		set_post_thumbnail( $post_id, $attach_id );
	}

	/**
	 * Update current version of logo, if we have got new one
	 * @param $post_id
	 * @param $image_url
	 */
	protected function updated_fetured_image( $post_id, $image_url ) {
		$this->delete_attachments_with_post($post_id);
		$old_image_url = get_post_meta( $post_id, 'lottery_logo', true );
		if ( $image_url == $old_image_url ) {
			return;
		} else {
			$post_title = get_the_title( $post_id );
			$this->set_featured_image( $post_id, $image_url, $post_title );
		}
	}

	/***
	 * clean up lottery list
	 */
	protected function remove_old_data(){
		$list = get_posts('numberposts=-1&post_type=lottery&post_status=any' );
		foreach ($list as $post){
			$this->delete_attachments_with_post($post->ID);
			wp_delete_post($post->ID, true);
		}
	}

	/***
	 * Remove all attachments connected with the post
	 *
	 * @param $postid
	 */
	function delete_attachments_with_post( $postid ){
		$post = get_post( $postid );

		if( in_array($post->post_type, ['lottery']) ){
			$attachments = get_children( array( 'post_type'=>'attachment', 'post_parent'=>$postid ) );
			if( $attachments ){
				foreach( $attachments as $attachment ) wp_delete_attachment( $attachment->ID );
			}
		}
	}

}