<?php

use PHPUnit\Framework\TestCase;
use ResponsiveMenuPro\Collections\OptionsCollection;

class OptionsCollectionTest extends TestCase {

    private $options = [
        'foo' => 'bar',
        'baz' => 'moo'
    ];

    public function testCreationFromConstructor() {
        $collection = new OptionsCollection($this->options);
        $this->assertCount(2, $collection);
    }

    public function testAddingOptions() {
        $collection = new OptionsCollection($this->options);
        $this->assertCount(2, $collection);

        $collection->add(['moon' => 'rise']);
        $this->assertCount(3, $collection);
    }

    public function testAccessViaArray() {
        $collection = new OptionsCollection($this->options);
        $this->assertEquals('bar', $collection['foo']);
        $this->assertEquals('moo', $collection['baz']);
    }

    public function testRemoveViaArray() {
        $collection = new OptionsCollection($this->options);
        $this->assertCount(2, $collection);

        unset($collection['foo']);

        $this->assertCount(1, $collection);
        $this->assertNull($collection['foo']);
    }

    public function testSetViaArray() {
        $collection = new OptionsCollection($this->options);
        $this->assertCount(2, $collection);

        $collection['moon'] = 'rise';

        $this->assertCount(3, $collection);
        $this->assertEquals('rise', $collection['moon']);
    }

    public function testReturnArrayWhenAsked() {
        $collection = new OptionsCollection($this->options);
        $this->assertInternalType('array', $collection->toArray());
        $this->assertEquals($this->options, $collection->toArray());
    }

    public function testStringIsAlwaysReturnedFromConstructor() {
        $array = ['array' => ['moon' => 'rise']];
        $collection = new OptionsCollection($array);

        $this->assertEquals(json_encode($array['array']), $collection['array']);
    }

    public function testStringIsAlwaysReturned() {
        $collection = new OptionsCollection($this->options);
        $array = ['array' => ['moon' => 'rise']];
        $collection->add($array);

        $this->assertEquals(json_encode($array['array']), $collection['array']);
        $this->assertEquals('bar', $collection['foo']);
    }

    public function testCorrectActiveArrowIsReturned() {
        $collection = new OptionsCollection($this->options);
        $collection->add(['active_arrow_font_icon' => '']);
        $collection->add(['active_arrow_font_icon_type' => 'font-awesome']);
        $collection->add(['active_arrow_image' => '']);
        $collection->add(['active_arrow_image_alt' => '']);
        $collection->add(['active_arrow_shape' => 'foo']);

        $this->assertEquals('foo', $collection->getActiveArrow());

        $collection->add(['active_arrow_image' => 'bar']);
        $collection->add(['active_arrow_image_alt' => 'baz']);
        $this->assertEquals('<img alt="baz" src="bar" />', $collection->getActiveArrow());

        $collection->add(['active_arrow_font_icon' => 'cog']);
        $this->assertEquals('<i class="fa fa-cog"></i>', $collection->getActiveArrow());

        $collection->add(['active_arrow_font_icon_type' => 'glyphicon']);
        $this->assertEquals('<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>', $collection->getActiveArrow());

        $collection->add(['active_arrow_font_icon' => '<img src="cog" />']);
        $collection->add(['active_arrow_font_icon_type' => 'custom']);
        $this->assertEquals('<img src="cog" />', $collection->getActiveArrow());
    }

    public function testCorrectInActiveArrowIsReturned() {
        $collection = new OptionsCollection($this->options);
        $collection->add(['inactive_arrow_font_icon' => '']);
        $collection->add(['inactive_arrow_font_icon_type' => 'font-awesome']);
        $collection->add(['inactive_arrow_image' => '']);
        $collection->add(['inactive_arrow_image_alt' => '']);
        $collection->add(['inactive_arrow_shape' => 'foo']);

        $this->assertEquals('foo', $collection->getInActiveArrow());

        $collection->add(['inactive_arrow_image' => 'bar']);
        $collection->add(['inactive_arrow_image_alt' => 'baz']);
        $this->assertEquals('<img alt="baz" src="bar" />', $collection->getInActiveArrow());

        $collection->add(['inactive_arrow_font_icon' => 'cog']);
        $this->assertEquals('<i class="fa fa-cog"></i>', $collection->getInActiveArrow());

        $collection->add(['inactive_arrow_font_icon_type' => 'glyphicon']);
        $this->assertEquals('<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>', $collection->getInActiveArrow());

        $collection->add(['inactive_arrow_font_icon' => '<img src="cog" />']);
        $collection->add(['inactive_arrow_font_icon_type' => 'custom']);
        $this->assertEquals('<img src="cog" />', $collection->getInActiveArrow());
    }

    public function testCorrectTitleImageReturned() {
        $collection = new OptionsCollection($this->options);
        $collection->add(['menu_title_image' => '']);
        $collection->add(['menu_title_font_icon' => '']);
        $collection->add(['menu_title_font_icon_type' => 'font-awesome']);

        $this->assertNull($collection->getTitleImage());

        $collection->add(['menu_title_image' => 'bar']);
        $collection->add(['menu_title_image_alt' => 'baz']);
        $this->assertEquals('<img alt="baz" src="bar" />', $collection->getTitleImage());

        $collection->add(['menu_title_font_icon' => 'cog']);
        $this->assertEquals('<i class="fa fa-cog"></i>', $collection->getTitleImage());

        $collection->add(['menu_title_font_icon_type' => 'glyphicon']);
        $this->assertEquals('<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>', $collection->getTitleImage());

        $collection->add(['menu_title_font_icon' => '<img src="cog" />']);
        $collection->add(['menu_title_font_icon_type' => 'custom']);
        $this->assertEquals('<img src="cog" />', $collection->getTitleImage());

    }

    public function testCorrectButtonIconReturned() {
        $collection = new OptionsCollection($this->options);
        $collection->add(['button_image' => '']);
        $collection->add(['button_font_icon' => '']);
        $collection->add(['button_font_icon_type' => 'font-awesome']);

        $this->assertEquals('<span class="responsive-menu-pro-inner"></span>', $collection->getButtonIcon());

        $collection->add(['button_image' => 'foo']);
        $collection->add(['button_image_alt' => 'bar']);
        $this->assertEquals('<img alt="bar" src="foo" class="responsive-menu-pro-button-icon responsive-menu-pro-button-icon-active" />', $collection->getButtonIcon());

        $collection->add(['button_font_icon' => 'cog']);
        $this->assertEquals('<i class="fa fa-cog responsive-menu-pro-button-icon responsive-menu-pro-button-icon-active"></i>', $collection->getButtonIcon());

        $collection->add(['button_font_icon_type' => 'glyphicon']);
        $this->assertEquals('<span class="glyphicon glyphicon-cog responsive-menu-pro-button-icon responsive-menu-pro-button-icon-active" aria-hidden="true"></span>', $collection->getButtonIcon());

        $collection->add(['button_font_icon' => '<img src="cog" />']);
        $collection->add(['button_font_icon_type' => 'custom']);
        $this->assertEquals('<img src="cog" />', $collection->getButtonIcon());

    }

    public function testCorrectActiveButtonIconReturned() {
        $collection = new OptionsCollection($this->options);
        $collection->add(['button_image' => '']);
        $collection->add(['button_font_icon_when_clicked' => '']);
        $collection->add(['button_font_icon_when_clicked_type' => 'font-awesome']);

        $this->assertNull($collection->getButtonIconActive());

        $collection->add(['button_image' => 'foo']);
        $collection->add(['button_image_when_clicked' => 'bar']);
        $collection->add(['button_image_alt_when_clicked' => 'baz']);
        $this->assertEquals('<img alt="baz" src="bar" class="responsive-menu-pro-button-icon responsive-menu-pro-button-icon-inactive" />', $collection->getButtonIconActive());

        $collection->add(['button_font_icon_when_clicked' => 'cog']);
        $this->assertEquals('<i class="fa fa-cog responsive-menu-pro-button-icon responsive-menu-pro-button-icon-inactive"></i>', $collection->getButtonIconActive());

        $collection->add(['button_font_icon_when_clicked_type' => 'glyphicon']);
        $this->assertEquals('<span class="glyphicon glyphicon-cog responsive-menu-pro-button-icon responsive-menu-pro-button-icon-inactive" aria-hidden="true"></span>', $collection->getButtonIconActive());

        $collection->add(['button_font_icon_when_clicked' => '<img src="cog" />']);
        $collection->add(['button_font_icon_when_clicked_type' => 'custom']);
        $this->assertEquals('<img src="cog" />', $collection->getButtonIconActive());

    }

    public function testUsesFontIconsWorksCorrectly() {

        $collection = new OptionsCollection($this->options);
        $collection->add(['menu_font_icons' => '']);

        $collection->add(['button_font_icon' => '']);
        $collection->add(['button_font_icon_type' => '']);
        $collection->add(['button_font_icon_when_clicked' => '']);
        $collection->add(['button_font_icon_when_clicked_type' => '']);
        $collection->add(['active_arrow_font_icon' => '']);
        $collection->add(['active_arrow_font_icon_type' => '']);
        $collection->add(['inactive_arrow_font_icon' => '']);
        $collection->add(['inactive_arrow_font_icon_type' => '']);
        $collection->add(['menu_title_font_icon' => '']);
        $collection->add(['menu_title_font_icon_type' => '']);

        $this->assertEquals(null, $collection->usesFontIcons());

        $collection->add(['button_font_icon' => 'cog']);
        $collection->add(['button_font_icon_type' => 'font-awesome']);
        $this->assertEquals(['font-awesome'], $collection->usesFontIcons());

        $collection->add(['active_arrow_font_icon' => 'cog']);
        $collection->add(['active_arrow_font_icon_type' => 'glyphicon']);
        $this->assertEquals(['glyphicon', 'font-awesome'], $collection->usesFontIcons());

        $collection->add(['menu_title_font_icon' => 'cog']);
        $collection->add(['menu_title_font_icon_type' => 'custom']);
        $this->assertEquals(['glyphicon', 'font-awesome'], $collection->usesFontIcons());

    }

    public function testUsesMenuFontIconsWorksCorrectly() {
        $collection = new OptionsCollection($this->options);

        $collection->add(['button_font_icon' => '']);
        $collection->add(['button_font_icon_type' => '']);
        $collection->add(['button_font_icon_when_clicked' => '']);
        $collection->add(['button_font_icon_when_clicked_type' => '']);
        $collection->add(['active_arrow_font_icon' => '']);
        $collection->add(['active_arrow_font_icon_type' => '']);
        $collection->add(['inactive_arrow_font_icon' => '']);
        $collection->add(['inactive_arrow_font_icon_type' => '']);
        $collection->add(['menu_title_font_icon' => '']);
        $collection->add(['menu_title_font_icon_type' => '']);

        $collection->add(['menu_font_icons' => '']);
        $this->assertEquals(null, $collection->usesFontIcons());

        $collection->add(['menu_font_icons' => '{"id":["32","45","65","32"],"icon":["cog","saw","cog","cog"],"type":["font-awesome","glyphicon","font-awesome","font-awesome"]}']);
        $this->assertEquals(['glyphicon', 'font-awesome'], $collection->usesFontIcons());

        $collection->add(['menu_font_icons' => '{"id":["32","45"],"icon":["cog","saw"],"type":["font-awesome","font-awesome"]}']);
        $this->assertEquals(['font-awesome'], $collection->usesFontIcons());

    }

}