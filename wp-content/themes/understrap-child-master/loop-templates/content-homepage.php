<?php
/**
 * Homepage partial template.
 *
 * @package understrap
 */
?>

<div class="wrapper" id="page-wrapper">
  <div class="container" id="content" tabindex="-1">
    <div class="row">
      <div class="col-md-8 content-area" id="primary">
          <main class="site-main" id="main">
            <div class="main-article" style="background-color: lightblue">
                      homepage content

            </div>
          </main>
      </div>
      <div class="col-md-4" id="homepage-sidebar">
      </div>
    </div>
  </div>
</div>
<?php

the_content();
