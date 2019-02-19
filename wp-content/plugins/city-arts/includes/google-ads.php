<?php
/*
  all things required to build google ads


<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- ca 728x90 standard -->
<ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-3749514479005131" data-ad-slot="3345402152"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>

*/

function google_ads() {
  return [
    'landscape' => [
      'id' => '1507566484755-2',
      'style' => ''
    ],
    'landscape_bottom' => [
      'id' => '1507566484755-1',
      'style' => ''
    ],
    'medium' => [
      'id' => '1506555798490-1',
      'style' => 'height:250px; width:300px;'
    ],
    'halfpage' => [
      'id' => '1507525983729-0',
      'style' => 'height:600px; width:300px;'
    ]
  ];
}

function google_tag_html( $ad ) {
  $tag = google_ads()[$ad];
  $id = $tag['id'];
  $style = $tag['style'];

  return sprintf(
    '<div id=\'div-gpt-ad-%1$s\' style=\'%2$s\' ><script>googletag.cmd.push(function() { googletag.display(\'div-gpt-ad-%1$s\'); });</script></div>',
    $id, $style
  );
}

function ad_300x250_shortcode() {
  return sprintf('<div class="ad_300x250_sc_container float-md-right ml-md-4">%1$s</div>', ad_300x250_core() );
}

function ad_300x250_core() {
  $google_medium_responsive = '<!-- ca - medium rect responsive --><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-3749514479005131" data-ad-slot="3174011765" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
  //return sprintf('<div class="ad-300x250 mx-auto my-auto">%1$s</div>', google_tag_html( 'medium' ) );
  return sprintf('<div class="ad-300x250 mx-auto my-auto">%1$s</div>', $google_medium_responsive );
}

function ad_300x600_shortcode() {
  return sprintf('<div class="ad_300x600_sc_container float-md-right ml-md-4">%1$s</div>', google_tag_html( 'halfpage' ) );
}

function ad_300x600_core() {
  return sprintf('<div class="ad-300x600 mx-auto my-auto">%1$s</div>', google_tag_html( 'halfpage' ) );
}

function ad_728xlandscape_shortcode() {
  $google_728 = '<ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-3749514479005131" data-ad-slot="3345402152"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
  $html = '<div class="row no-gutters clearfix"><div class="col pb-3 text-center"><span style="font-size:.5em;">ADVERTISEMENT</span>';
  $html .= '<div class=" mx-auto " style="max-width:728px;max-height:90px;">%1$s</div></div></div>';
//  return sprintf($html, google_tag_html( 'landscape' ) );
  return sprintf($html, $google_728 );
}

function ad_728xlandscape_bottom_shortcode() {
  $html = '<div class="row no-gutters clearfix"><div class="col pb-3 text-center"><span style="font-size:.5em;">ADVERTISEMENT</span>';
  $html .= '<div class=" mx-auto " style="max-width:728px;max-height:90px;">%1$s</div></div></div>';
  return sprintf($html, google_tag_html( 'landscape_bottom' ) );
}
