<?php
/**
 * @package Twitter_Scroller
 * @version 1.0
 */
/*
Plugin Name: Twitter Feed Scroller
Plugin URI: http://www.github.com/johnernaut/twitter-scroller
Description: Automatically pull your latest tweets into your widget areas and have them displayed within a slick news-scroller styled container.
Author: John Johnson
Version: 1.0
Author URI: http://johnejohnson.org
*/
?>

<?php
class Twitter_Scroller extends WP_Widget {

	function Twitter_Scroller() {	
	$widget_ops = array( 'classname' => 'twitter_widget', 'description' => 'Show off your recent tweets!' );
		$this->WP_Widget( 'twitter_widget', 'Twitter Feed', $widget_ops);
	}
	
	function form($instance) {
	
		
		$instance = wp_parse_args( (array) $instance, array('title' => 'Twitter Feed', 'number' => 5, 'twitter_username' => '') );

        $title = esc_attr($instance['title']);
        $twitter_username = $instance['twitter_username'];
		$number = absint($instance['number']);

?>
		<p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
               Title:
            </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

		

		<p>
            <label for="<?php echo $this->get_field_id('twitter_username'); ?>">
               Twitter username:
            </label>
                <input class="widefat" id="<?php echo $this->get_field_id('twitter_username'); ?>" name="<?php echo $this->get_field_name('twitter_username'); ?>" type="text" value="<?php echo $twitter_username; ?>" />
                
        </p>

		<p>

		<p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
               Number of Tweets:
            </label>
                <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
        </p>


<?php
    }

	function update($new_instance, $old_instance) {
        $instance=$old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['twitter_username']=$new_instance['twitter_username'];
        $instance['number']=$new_instance['number'];
        return $instance;

    }

	function widget($args, $instance) {
	
		extract($args);

		$title = apply_filters('widget_title', $instance['title']);
		if ( empty($title) ) $title = false;

        $twitter_username = $instance['twitter_username'];
		$number = absint( $instance['number'] );
		
		
		if (!empty($twitter_username)) {
		
			
			echo $before_widget;
		
			if($title){
				echo $before_title;
				echo $title; 
				echo $after_title;
			} ?>
			
			<?php
			$twitter_raw = file_get_contents('http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=' . $twitter_username);

			$twitter = array();

			$twitter_xml = new SimpleXMLElement($twitter_raw);
			foreach($twitter_xml->channel->item as $item) {
			//replace 'johnernaut' with your username if you'd like to remove it from the description
			$description = trim(str_replace($twitter_username . ':', '', $item->description));
			$twitter_item = array(
			'content' => $description,
			'date' => strtotime($item->pubDate),
			'type' => 'Twitter'
			);
			array_push($twitter, $twitter_item);
			}

			$t_index = 0;
			$count = 0;
			$twitter_final = array();
			//logic to only display defined number of tweets
			while($count < $number) {
			if(isset($twitter[$t_index])) {
			array_push($twitter_final, $twitter[$t_index]);
			$t_index++;
			} else {
			throw new Exception("nope");
			}
			$count++;
			}
			?>
					
    		<ul id="popular">
				<?php //the quick, dirty way to display our tweets in a somewhat useable format
				foreach($twitter_final as $item) {
				echo '<li>' . $item["content"]
				. '<a href="#">' . date(DATE_RFC822, $item["date"]) . '</a>'
				. '</li>';
				} ?>
			</ul>
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#popular')
		            .children('li:not(:first)')
		            .hide();
					setInterval(function() {
		                jQuery('#popular').children('li:visible').fadeOut(200,
		                function() {
		                    jQuery(this).index() === jQuery(this).parent().children().length - 1
		                    ? jQuery(this).parent().children("li").eq(0).fadeIn(100)
		                    : jQuery(this).next().fadeIn(100);
		                });
		            },
		            2000);
				});
			</script>
			
			<?php
			echo $after_widget;

		}
		
	}

}

add_action( 'widgets_init', 'pg_load_widgets' );
function pg_load_widgets() {
	register_widget('Twitter_Scroller');
}
?>