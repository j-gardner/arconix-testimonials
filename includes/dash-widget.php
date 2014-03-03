<?php
echo '<div class="rss-widget">';

wp_widget_rss_output( array(
    'url' => 'http://arconixpc.com/tag/arconix-testimonials/feed', // feed url
    'title' => 'Arconix Testimonials Posts', // feed title
    'items' => 3, // how many posts to show
    'show_summary' => 1, // display excerpt
    'show_author' => 0, // display author
    'show_date' => 1 // display post date
) );

echo '<div class="act-widget-bottom"><ul>';
?>
    <li><a href="http://arcnx.co/atwiki" class="atdocs"><img src="<?php echo ACT_IMAGES_URL . 'page-16x16.png' ?>">Documentation</a></li>
    <li><a href="http://arcnx.co/athelp" class="athelp"><img src="<?php echo ACT_IMAGES_URL . 'help-16x16.png' ?>">Support Forum</a></li>
    <li><a href="http://arcnx.co/attrello" class="atdev"><img src="<?php echo ACT_IMAGES_URL . 'trello-16x16.png' ?>">Dev Board</a></li>
    <li><a href="http://arcnx.co/atsource" class="atsource"><img src="<?php echo ACT_IMAGES_URL . 'github-16x16.png'; ?>">Source Code</a></li>
<?php
echo '</ul></div>';
echo '</div>';