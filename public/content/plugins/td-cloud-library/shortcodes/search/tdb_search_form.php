<?php
/**
 * Class tdb_search_form
 */

class tdb_search_form extends td_block {

    public function get_custom_css() {
        // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $unique_block_class = $this->block_uid . '_rand';

        $compiled_css = '';

        $raw_css =
            "<style>

				/* @msg_margin */
				.$unique_block_class .tdb-search-msg {
					margin-top: @msg_margin;
				}

				/* @align_center */
				.td-theme-wrap .$unique_block_class {
					text-align: center;
				}
				/* @align_right */
				.td-theme-wrap .$unique_block_class {
					text-align: right;
				}
				/* @align_left */
				.td-theme-wrap .$unique_block_class {
					text-align: left;
				}
				
				/* @input_text */
				.$unique_block_class .tdb-search-input {
					color: @input_text;
				}
				/* @input_bg */
				.$unique_block_class .tdb-search-input {
					background-color: @input_bg;
				}
				/* @input_border */
				.$unique_block_class .tdb-search-input {
					border-color: @input_border;
				}
				/* @input_border_h */
				.$unique_block_class .tdb-search-input:focus {
					border-color: @input_border_h !important;
				}
				/* @border_size */
				.$unique_block_class .tdb-search-input {
					border-width: @border_size;
				}
				/* @border_radius */
				.$unique_block_class .tdb-search-input {
					border-top-left-radius: @border_radius;
					border-bottom-left-radius: @border_radius;
				}
				.$unique_block_class .btn {
					border-top-right-radius: @border_radius;
					border-bottom-right-radius: @border_radius;
				}
				/* @btn_border_size */
				.$unique_block_class .btn {
					border-width: @btn_border_size;
					border-style: solid;
					border-color: #444;
				}
				
				/* @btn_text_color */
				.$unique_block_class .btn {
					color: @btn_text_color;
				}
				/* @btn_text_h */
				.$unique_block_class .btn:hover {
					color: @btn_text_h;
				}
				/* @btn_bg */
				.$unique_block_class .btn {
					background-color: @btn_bg;
				}
				/* @btn_bg_h */
				.$unique_block_class .btn:hover {
					background-color: @btn_bg_h;
				}
				/* @btn_border */
				.$unique_block_class .btn {
					border-color: @btn_border;
				}
				/* @btn_border_h */
				.$unique_block_class .btn:hover {
					border-color: @btn_border_h;
				}
				
				/* @msg_color */
				.$unique_block_class .tdb-search-msg {
					color: @msg_color;
				}
				
				
				
				/* @f_input */
				.$unique_block_class .tdb-search-input {
				    @f_input
				}
				/* @f_btn */
				.$unique_block_class .btn {
				    @f_btn
				}
				/* @f_msg */
				.$unique_block_class .tdb-search-msg {
				    @f_msg
				}
				
			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;
    }

    static function cssMedia( $res_ctx ) {

        // content align
        $msg_margin = $res_ctx->get_shortcode_att('msg_margin');
        if( $msg_margin != '' ) {
            if( is_numeric( $msg_margin ) ) {
                $res_ctx->load_settings_raw( 'msg_margin', $res_ctx->get_shortcode_att('msg_margin') . 'px' );
            }
        } else {
            $res_ctx->load_settings_raw( 'msg_margin', '11px' );
        }

        // content align
        $content_align = $res_ctx->get_shortcode_att('content_align_horizontal');
        if ( $content_align == 'content-horiz-center' ) {
            $res_ctx->load_settings_raw( 'align_center', 1 );
        } else if ( $content_align == 'content-horiz-right' ) {
            $res_ctx->load_settings_raw( 'align_right', 1 );
        } else if ( $content_align == 'content-horiz-left' ) {
            $res_ctx->load_settings_raw( 'align_left', 1 );
        }

        // border size
        $border_size = $res_ctx->get_shortcode_att('border_size');
        $res_ctx->load_settings_raw( 'border_size', $border_size );
        if( $border_size != '' && is_numeric( $border_size ) ) {
            $res_ctx->load_settings_raw( 'border_size', $border_size . 'px' );
        }

        // border radius
        $border_radius = $res_ctx->get_shortcode_att('border_radius');
        $res_ctx->load_settings_raw( 'border_radius', $border_radius );
        if( $border_radius != '' && is_numeric( $border_radius ) ) {
            $res_ctx->load_settings_raw( 'border_radius', $border_radius . 'px' );
        }

        // button border size
        $btn_border_size = $res_ctx->get_shortcode_att('btn_border_size');
        $res_ctx->load_settings_raw( 'btn_border_size', $btn_border_size );
        if( $btn_border_size != '' && is_numeric( $btn_border_size ) ) {
            $res_ctx->load_settings_raw( 'btn_border_size', $btn_border_size . 'px' );
        }

        // colors
        $res_ctx->load_settings_raw( 'input_text', $res_ctx->get_shortcode_att('input_text') );
        $res_ctx->load_settings_raw( 'input_bg', $res_ctx->get_shortcode_att('input_bg') );
        $res_ctx->load_settings_raw( 'input_border', $res_ctx->get_shortcode_att('input_border') );
        $res_ctx->load_settings_raw( 'input_border_h', $res_ctx->get_shortcode_att('input_border_h') );
        $res_ctx->load_settings_raw( 'btn_text_color', $res_ctx->get_shortcode_att('btn_text_color') );
        $res_ctx->load_settings_raw( 'btn_text_h', $res_ctx->get_shortcode_att('btn_text_h') );
        $res_ctx->load_settings_raw( 'btn_bg', $res_ctx->get_shortcode_att('btn_bg') );
        $res_ctx->load_settings_raw( 'btn_bg_h', $res_ctx->get_shortcode_att('btn_bg_h') );
        $res_ctx->load_settings_raw( 'btn_border', $res_ctx->get_shortcode_att('btn_border') );
        $res_ctx->load_settings_raw( 'btn_border_h', $res_ctx->get_shortcode_att('btn_border_h') );
        $res_ctx->load_settings_raw( 'msg_color', $res_ctx->get_shortcode_att('msg_color') );



        /*-- FONTS -- */
        $res_ctx->load_font_settings( 'f_input' );
        $res_ctx->load_font_settings( 'f_btn' );
        $res_ctx->load_font_settings( 'f_msg' );

    }

    // disable loop block features. This block does not use a loop and it doesn't need to run a query.
    function __construct() {
        parent::disable_loop_block_features();
    }


    function render( $atts, $content = null ) {
        parent::render( $atts );

        global $tdb_state_search;
        $search_form_data = $tdb_state_search->search_form->__invoke( $atts );

        $message = $this->get_att( 'message' );

        $buffy = ''; //output buffer

        $buffy .= '<div class="' . $this->get_block_classes() . '" ' . $this->get_block_html_atts() . '>';

            //get the block css
            $buffy .= $this->get_block_css();

            //get the js for this block
            $buffy .= $this->get_block_js();


            $buffy .= '<div class="tdb-block-inner td-fix-index">';

                $buffy .= '<form method="get" class="tdb-search-form" action="' . esc_url(home_url( '/' )) . '">';
                    $buffy .= '<div role="search" class="tdb-search-form-inner">';
                        $buffy .= '<input class="tdb-search-input" type="text" value="' . $search_form_data['search_query'] . '" name="s" id="s" />';
                        $buffy .= '<input class="wpb_button wpb_btn-inverse btn" type="submit" id="searchsubmit" value="' . $this->get_att( 'btn_text' ) . '" />';
                    $buffy .= '</div>';
                $buffy .= '</form>';

                if( $message != '' ) {
                    $buffy .= '<div class="tdb-search-msg">';
                        $buffy .= rawurldecode( base64_decode( strip_tags( $message ) ) );
                    $buffy .= '</div>';
                }

            $buffy .= '</div>';

        $buffy .= '</div>';

        return $buffy;
    }


}