<?php

namespace ResponsiveMenuPro\Collections;

class OptionsCollection implements \ArrayAccess, \Countable {

    private $options;

    public function __construct(array $options = []) {
        $this->options = array_map(function($o) {
           return is_array($o) ? json_encode($o) : $o;
        }, $options);
    }

    public function add(array $option) {
        $value = $option[key($option)];
        $this->options[key($option)] = is_array($value) ? json_encode($value) : $value;
    }

    public function usesFontIcons() {

        $font_icon_array = (array) json_decode($this->options['menu_font_icons']);

        $font_icons = isset($font_icon_array['type']) ? array_filter($font_icon_array['type'], function($a) {
            return $a;
        }) : null;

        if(
            $this->options['button_font_icon']
            || $this->options['button_font_icon_when_clicked']
            || $this->options['active_arrow_font_icon']
            || $this->options['inactive_arrow_font_icon']
            || $this->options['menu_title_font_icon']
            || !empty($font_icons)
        ):

            $font_icon_types = [
                $this->options['button_font_icon'] ? $this->options['button_font_icon_type'] : '',
                $this->options['button_font_icon_when_clicked'] ? $this->options['button_font_icon_when_clicked_type'] : '',
                $this->options['active_arrow_font_icon'] ? $this->options['active_arrow_font_icon_type'] : '',
                $this->options['inactive_arrow_font_icon'] ? $this->options['inactive_arrow_font_icon_type'] : '',
                $this->options['menu_title_font_icon'] ? $this->options['menu_title_font_icon_type'] : '',
            ];

            $font_icon_types_used = [];

            if((isset($font_icon_array['type']) && in_array('glyphicon', $font_icon_array['type'])) || in_array('glyphicon', $font_icon_types))
                $font_icon_types_used[] = 'glyphicon';

            if((isset($font_icon_array['type']) && in_array('font-awesome', $font_icon_array['type'])) || in_array('font-awesome', $font_icon_types))
                $font_icon_types_used[] = 'font-awesome';

            return $font_icon_types_used;

        endif;

        return false;
    }

    public function getMenuFontIcon($id) {

        if($this->options['menu_font_icons']):
            $icons = json_decode($this->options['menu_font_icons'], true);
            $key = array_search($id, $icons['id']);

            if(is_int($key)):
                switch($icons['type'][$key]):
                    case 'glyphicon':
                        return '<span class="glyphicon glyphicon-' . $icons['icon'][$key] . '" aria-hidden="true"></span>';
                        break;
                    case 'font-awesome':
                        return '<i class="fa fa-' . $icons['icon'][$key] .'"></i>';
                        break;
                    default:
                        return $icons['icon'][$key];
                    endswitch;
            endif;
        endif;


        return '';

    }

    public function getActiveArrow() {

        if($this->options['active_arrow_font_icon']):
            switch($this->options['active_arrow_font_icon_type']):
                case 'glyphicon':
                    return '<span class="glyphicon glyphicon-' . $this->options['active_arrow_font_icon'] . '" aria-hidden="true"></span>';
                    break;
                case 'font-awesome':
                    return '<i class="fa fa-' . $this->options['active_arrow_font_icon'] .'"></i>';
                    break;
                default:
                    return $this->options['active_arrow_font_icon'];
                endswitch;
        endif;

        if($this->options['active_arrow_image'])
            return '<img alt="' . $this->options['active_arrow_image_alt'] .'" src="' . $this->options['active_arrow_image'] .'" />';

        return $this->options['active_arrow_shape'];

    }

    public function getInActiveArrow() {
        if($this->options['inactive_arrow_font_icon']):
            switch($this->options['inactive_arrow_font_icon_type']):
                case 'glyphicon':
                    return '<span class="glyphicon glyphicon-' . $this->options['inactive_arrow_font_icon'] . '" aria-hidden="true"></span>';
                    break;
                case 'font-awesome':
                    return '<i class="fa fa-' . $this->options['inactive_arrow_font_icon'] .'"></i>';
                    break;
                default:
                    return $this->options['inactive_arrow_font_icon'];
            endswitch;
        endif;

        if($this->options['inactive_arrow_image'])
            return '<img alt="' . $this->options['inactive_arrow_image_alt'] .'" src="' . $this->options['inactive_arrow_image'] .'" />';

        return $this->options['inactive_arrow_shape'];

    }

    public function getTitleImage() {

        if($this->options['menu_title_font_icon']):
            switch($this->options['menu_title_font_icon_type']):
                case 'glyphicon':
                    return '<span class="glyphicon glyphicon-' . $this->options['menu_title_font_icon'] . '" aria-hidden="true"></span>';
                    break;
                case 'font-awesome':
                    return '<i class="fa fa-' . $this->options['menu_title_font_icon'] .'"></i>';
                    break;
                default:
                    return $this->options['menu_title_font_icon'];
            endswitch;
        endif;

        if($this->options['menu_title_image'])
            return '<img alt="' . $this->options['menu_title_image_alt'] .'" src="' . $this->options['menu_title_image'] .'" />';

        return null;

    }

    public function getButtonIcon() {

        if($this->options['button_font_icon']):
            switch($this->options['button_font_icon_type']):
                case 'glyphicon':
                    return '<span class="glyphicon glyphicon-' . $this->options['button_font_icon'] . ' responsive-menu-pro-button-icon responsive-menu-pro-button-icon-active" aria-hidden="true"></span>';
                    break;
                case 'font-awesome':
                    return '<i class="fa fa-' . $this->options['button_font_icon'] .' responsive-menu-pro-button-icon responsive-menu-pro-button-icon-active"></i>';
                    break;
                default:
                    return $this->options['button_font_icon'];
            endswitch;
        endif;

        if($this->options['button_image'])
            return '<img alt="' . $this->options['button_image_alt'] .'" src="' . $this->options['button_image'] .'" class="responsive-menu-pro-button-icon responsive-menu-pro-button-icon-active" />';

        return '<span class="responsive-menu-pro-inner"></span>';
    }

    public function getButtonIconActive() {

        if($this->options['button_font_icon_when_clicked']):
            switch($this->options['button_font_icon_when_clicked_type']):
                case 'glyphicon':
                    return '<span class="glyphicon glyphicon-' . $this->options['button_font_icon_when_clicked'] . ' responsive-menu-pro-button-icon responsive-menu-pro-button-icon-inactive" aria-hidden="true"></span>';
                    break;
                case 'font-awesome':
                    return '<i class="fa fa-' . $this->options['button_font_icon_when_clicked'] .' responsive-menu-pro-button-icon responsive-menu-pro-button-icon-inactive"></i>';
                    break;
                default:
                    return $this->options['button_font_icon_when_clicked'];
            endswitch;
        endif;

        if($this->options['button_image'])
            return '<img alt="' . $this->options['button_image_alt_when_clicked'] .'" src="' . $this->options['button_image_when_clicked'] .'" class="responsive-menu-pro-button-icon responsive-menu-pro-button-icon-inactive" />';

    }

    public function offsetExists($offset) {
        return array_key_exists($offset, $this->options);
    }

    public function offsetGet($offset) {
        if(isset($this->options[$offset]))
            return $this->options[$offset];
        return null;
    }

    public function offsetSet($offset, $value) {
        $this->add([$offset => $value]);
    }

    public function offsetUnset($offset) {
        if(isset($this->options[$offset]))
            unset($this->options[$offset]);
    }

    public function toArray() {
        $array = [];
        foreach($this->options as $key => $val)
            $array[$key] = $val;
        return $array;
    }

    public function count() {
        return count($this->options);
    }

}
