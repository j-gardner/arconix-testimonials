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
    <li><a href="http://arcnx.co/atwiki"><img src="<?php echo ACT_IMAGES_URL . 'page-16x16.png' ?>">Documentation</a></li>
    <li><a href="http://arcnx.co/athelp"><img src="<?php echo ACT_IMAGES_URL . 'help-16x16.png' ?>">Support Forum</a></li>
    <li><a href="http://arcnx.co/attrello"><img src="<?php echo ACT_IMAGES_URL . 'trello-16x16.png' ?>">Dev Board</a></li>
    <li><a href="http://arcnx.co/atsource"><img src="<?php echo ACT_IMAGES_URL . 'github-16x16.png'; ?>">Source Code</a></li>
<?php
echo '</ul></div>';
echo '</div>';

// handle the styling
echo '<style type="text/css">
        #ac-testimonials .rsssummary { display: block; }
        #ac-testimonials .act-widget-bottom { border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
        #ac-testimonials .act-widget-bottom ul { list-style: none; }
        #ac-testimonials .act-widget-bottom ul li { display: inline; padding-right: 20px; }
        #ac-testimonials .act-widget-bottom img { padding-right: 3px; vertical-align: top; }
    </style>';