<?php 

/**
 * Plugin Name: IU Instagram Widget
 * Plugin URI: https://iunique.com.br
 * Description: Plugin created to show instagram photos on widget
 * Version: 1.0
 * Author: Thomas Kauai / Iunique
 * Author URI: https://iunique.com.br
 */

define( 'IU_INSTAGRAM_PATH', untrailingslashit(plugins_url('', __FILE__)) );


//WIDGET DO INSTAGRAM
add_action( 'widgets_init', 'iu_insta_init' );
function iu_insta_init() {register_widget( 'iu_insta_widget' );}

class iu_insta_widget extends WP_Widget {

  public function __construct() {
    $widget_details = array(
        'classname' => 'iu_insta_widget',
        'description' => 'Widget usado para mostrar as fotos do Instagram na lateral do blog'
    );
    parent::__construct( 'iu_insta_widget', 'iU Instagram', $widget_details );
  }

  public function update( $new_instance, $old_instance ) {  
      return $new_instance;
  }

  public function form( $instance ) {
    $title = ''; if( !empty( $instance['title'] ) ) {$title = $instance['title'];}    
    $username = ''; if( !empty( $instance['username'] ) ) {$username = $instance['username'];}  
    $token = ''; if( !empty( $instance['token'] ) ) {$token = $instance['token'];}  
    $quantity = ''; if( !empty( $instance['quantity'] ) ) {$quantity = $instance['quantity'];}
    $cols = ''; if( !empty( $instance['cols'] ) ) {$cols = $instance['cols'];}
  ?>

  <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>">Título:</label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
  </p>

  <p>
    <label for="<?php echo $this->get_field_id( 'username' ); ?>">Usuário do Instagram</label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" placeholder="Usuário do Instagram" />
  </p>

	<p>
    <label for="<?php echo $this->get_field_id( 'token' ); ?>">Access Token <?php if (isset($_POST) && $token == '') {echo '<span class="alert">*recomendado</span>';} ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'token' ); ?>" name="<?php echo $this->get_field_name( 'token' ); ?>" type="text" value="<?php echo esc_attr( $token ); ?>" placeholder="Token de acesso"/>
    <span><span class="dashicons dashicons-info"></span>Sem utilizar o Token a exibição das fotos pode parar de funcionar a qualquer momento. O Instagram agora exige um token de acesso para autorizar a exibição das fotos. Para gerar, você pode <a href="http://instagram.pixelunion.net/" target="_blank">acessar esse link</a> e clicar em <b>"Generate Access Token"</b>.</span>
  </p>
  
  <p class="insta cols wp-clearfix">
    <label class="title">Quantas colunas de fotos?</label>        
    <input type="radio" name="<?php echo $this->get_field_name('cols'); ?>" value="cols2" id="<?php echo $this->get_field_name('cols'); ?>cols2" <?php checked('cols2', $cols); ?>/>
    <label for="<?php echo $this->get_field_name('cols'); ?>cols2"><img src="<?php echo IU_INSTAGRAM_PATH ?>/assets/img/cols2.png"></label>
        
    <input type="radio" name="<?php echo $this->get_field_name('cols'); ?>" value="cols3" id="<?php echo $this->get_field_name('cols'); ?>cols3" <?php checked('cols3', $cols); ?>/>    
    <label for="<?php echo $this->get_field_name('cols'); ?>cols3"><img src="<?php echo IU_INSTAGRAM_PATH ?>/assets/img/cols3.png"></label>
  </p>

  <p>
    <label for="<?php echo $this->get_field_name( 'quantity' ); ?>">Quantidade de fotos</label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'quantity' ); ?>" name="<?php echo $this->get_field_name( 'quantity' ); ?>" type="number" min="1" max="9" value="<?php echo esc_attr( $quantity ); ?>"/>
  </p>

  <style>    
    .alert {color: indianred;}
    input + span {display: block; margin-top: 5px; line-height: 1.2; color: #777}
    input + span .dashicons {font-size: 18px; height: 0; width: auto; margin-top: -1px;}
    .insta.cols label.title {display: block; margin-bottom: 10px;}
    .cols input[type=radio] {display: none;}
    .cols input + label {float: left; margin-right: 15px; border: 1px solid #e6e6e6;} 
    .insta.cols label img {opacity: .6}
    .cols input[type="radio"]:checked+label {border: 1px solid #ccc; outline: 1px solid #ccc}
    .cols input[type="radio"]:checked+label img {opacity: 1;}
  </style>

  <?php
  }

  //SAÍDA DO WIDGET
  public function widget( $args, $instance ) {
    echo $args['before_widget'];
    if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
    }
  ?>
  
  <?php if($instance['username'] !== '') { ?>   

    <?php
    $username = str_replace( '@', '', $instance['username'] );
    $token = $instance['token'];
    $limit = $instance['quantity']; //quantidade de itens
    
    if($limit > 10) {$limit = 10;}
    if(!$instance['cols']) {$instance['cols'] = 3;}

    if($token) {
      $url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $token . '&count=' . $limit;
    } else {
      $url = 'https://www.instagram.com/'. $username .'/?__a=1';  
    }

    $remote = wp_remote_get( $url );

  	if(!is_wp_error($remote)) {
    	$instagram_feed_data = json_decode($remote[body], true);	                 
    }
    			        	  
    if(is_wp_error($remote)) {
      echo "<div style='text-align: center;'>A sua hospedagem parace estar bloqueando a função que carrega as imagens do Instagram. Entre em contato como suporte perguntanto se o servidor está configurado para bloquear requisições HTTP. Se sim, terá que pedir para liberar.</div>";
    }
    
    else if($token && !$instagram_feed_data['data']) {
      echo "<div style='text-align: center;'>O token que você informou parace ser inválido! Tente gerar um novo token e inserir novamente.</div>";
    }    
    
    else { //HAVE DATA

      if($token) {
        $images = $instagram_feed_data['data'];
      } else {
        $images = $instagram_feed_data['graphql']['user']['edge_owner_to_timeline_media']['edges'];
      }

    ?>
    <div class="insta-grid clearfix <?php echo $instance['cols']; ?>">  
      <?php $count == 0;

      foreach ($images as $image) {
        $count++;
        if($count > $limit) {
          break;
        }
        
        if($token) {
          $img_url_thumb = $image['images']['thumbnail']['url']; //thumbnail / low_resolution / standard_resolution
          $link = $image['link'];
          $caption = $image['caption']['text'];
        } else {
          $img_url_thumb = preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] );
          $link = trailingslashit( '//www.instagram.com/p/' . $image['node']['shortcode'] );
        }
        
        ?>
        <a href="<?php echo $link; ?>" target="_blank" class="item instagram-post">
          <img src="<?php echo $img_url_thumb; ?>">
        </a>
      <?php } ?>	
    </div><!--instagram-grid-->
    <?php } ?>

  <?php } //usarneme

  echo $args['after_widget']; 

  } //FIM saída widget    
}

function wp_load_style() {
    wp_enqueue_style( 'iu-instagram-style', IU_INSTAGRAM_PATH. '/assets/css/insta-widget.css' );
}
add_action('wp_enqueue_scripts', 'wp_load_style', 99);
