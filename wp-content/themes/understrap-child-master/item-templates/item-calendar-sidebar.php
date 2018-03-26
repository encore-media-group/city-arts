<?php
/**
 * Single item landscape ad template.
 *
 * @package understrap
 */


$subtitle = "Our picks for this month";
?>

<div class="row my-5">
  <div class="col mx-3 mt-4 calendar-sidebar text-center current">
    <h2 class="sidebar">Calendar</h2>
    <h6 class="current-text"><?= $subtitle ?></h4>
    <div class="row">
      <div class="col-12 col-lg-10 mx-auto px-0 listings">
        <?php
          $calendar_disciplines = get_calendar_disciplines();
          echo '<ul class="nav flex-column mt-3">';
          foreach ($calendar_disciplines as $calendar_discipline) :
            echo sprintf('<li class="nav-item"><a href="/calendar/%1$s">%2$s</a></li>', $calendar_discipline['slug'], $calendar_discipline['name'] );
          endforeach;
          echo '</ul>';
          ?>
      </div>
    </div>

  </div>
</div> <!-- .row -->
