<?php

namespace ResponsiveMenuPro\Walkers;
use ResponsiveMenuPro\Collections\OptionsCollection;

class Walker extends \Walker_Nav_Menu {

    private $current_item;

    public function __construct(OptionsCollection $options) {
        $this->options = $options;
    }

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {

        $this->setCurrentItem($item);

        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $responsive_menu_pro_classes = $classes;

        # Turn into our Responsive Menu Classes
        foreach($classes as $class):
            switch($class):
                case 'menu-item': $responsive_menu_pro_classes[] = 'responsive-menu-pro-item'; break;
                case 'current-menu-item': $responsive_menu_pro_classes[] = 'responsive-menu-pro-current-item'; break;
                case 'menu-item-has-children': $responsive_menu_pro_classes[] = 'responsive-menu-pro-item-has-children'; break;
                case 'current-menu-parent': $responsive_menu_pro_classes[] = 'responsive-menu-pro-item-current-parent'; break;
                case 'current-menu-ancestor': $responsive_menu_pro_classes[] = 'responsive-menu-pro-item-current-ancestor'; break;
            endswitch;
        endforeach;

        /* Clear child class if we are at the final depth level */
        if(isset($responsive_menu_classes)):
            if($depth + 1 == $this->options['menu_depth'] && ($key = array_search('responsive-menu-pro-item-has-children', $responsive_menu_classes)) !== false) {
                unset($responsive_menu_classes[$key]);
            }
        endif;

        $class_names = join(' ', array_unique($responsive_menu_pro_classes));
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = ' id="responsive-menu-pro-item-' . esc_attr( $item->ID ) . '"';

        $output .= '<li' . $id . $class_names .'>';

        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        $atts['class']   = 'responsive-menu-pro-item-link';

        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }


        $title = apply_filters('the_title', $item->title, $item->ID);
        $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

        /* Calculate which arrow to show */
        if(in_array('responsive-menu-pro-item-has-children', $responsive_menu_pro_classes)):
            $inactive_arrow = '<div class="responsive-menu-pro-subarrow">' . $this->options->getInActiveArrow() . '</div>';
            $active_arrow = '<div class="responsive-menu-pro-subarrow responsive-menu-pro-subarrow-active">' . $this->options->getActiveArrow()  . '</div>';
            if($this->options['auto_expand_all_submenus'] == 'on'):
                $initial_arrow = $active_arrow;
            elseif(
                $this->options['auto_expand_current_submenus'] == 'on' && (in_array('responsive-menu-pro-item-current-parent', $responsive_menu_pro_classes)
                    || in_array('responsive-menu-pro-item-current-ancestor', $responsive_menu_pro_classes))):
                $initial_arrow = $active_arrow;
            else:
                $initial_arrow = $inactive_arrow;
            endif;
        else:
            $initial_arrow = '';
        endif;

        /* Clear Arrow if we are at the final depth level */
        if($depth + 1 == $this->options['menu_depth'])
            $initial_arrow = '';

        /* Get Font Icon Information */
        $font_icon = $this->options->getMenuFontIcon($item->ID);

        $item_output = '<a'. $attributes .'>' . $font_icon;
        $item_output .= $title;
        $item_output .= $initial_arrow;
        $item_output .= '</a>';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);

    }

    public function start_lvl(&$output, $depth = 0, $args = array()) {

        $output .= "<ul data-depth='" . ($depth + 2) . "' class='responsive-menu-pro-submenu responsive-menu-pro-submenu-depth-" . ($depth + 1) . $this->getSubmenuClassOpenOrNot() . "'>";

        if($this->options['use_slide_effect'] == 'on')
            $output .= '<div class="responsive-menu-pro-back">' . $this->options->getActiveArrow() . $this->options['slide_effect_back_to_text'] . '</div>';

    }

    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        $output .= "</li>";
    }

    public function end_lvl(&$output, $depth = 0, $args = array()) {
        $output .= "</ul>";
    }

    public function setCurrentItem($item) {
        $this->current_item = $item;
    }

    public function getCurrentItem() {
        return $this->current_item;
    }

    public function getSubmenuClassOpenOrNot() {
        return $this->expandAllSubmenuOptionsIsOn() || $this->expandCurrentSubmenuOnAndItemIsParent() ? ' responsive-menu-pro-submenu-open' : '';
    }

    public function expandAllSubmenuOptionsIsOn() {
        return $this->options['auto_expand_all_submenus'] == 'on';
    }

    public function expandCurrentSubmenuOnAndItemIsParent() {
        return ($this->options['auto_expand_current_submenus'] == 'on')
            && ($this->getCurrentItem()->current_item_ancestor || $this->getCurrentItem()->current_item_parent);
    }

}