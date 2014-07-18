<!-- FACEBOOK JOB LISTINGS IFRAME -->
<head>
     <title>Career Opportunities</title>
     <!--<link rel="stylesheet" type="text/css" href="style.css"/>-->
     <script type="text/javascript">
     window.fbAsyncInit = function() {
          FB.Canvas.setSize({ width: 800, height: 600 });
          //FB.Canvas.setAutoGrow(false);
     }
     // Do things that will sometimes call sizeChangeCallback()
     function sizeChangeCallback() {
          FB.Canvas.setSize({ width: 800, height: 600 });
     }
     </script>
     <style type="text/css">
     <!--
     .views-field-title {
          width: 45% !important;
     }
     
     .views-field-field-status {
          width: 10% !important;
     }
     
     .views-field-field-practice-group-dept {
          width: 43% !important;
     }
     -->
     </style>
</head>
<body style="margin:0px; padding:0px; width:810px; height:600px; overflow:hidden">
     <h2>Career Opportunities with University of Louisville Physicians</h2>
     <p>Click the links below to learn more about the position that interests you and to apply:</p>
<?php
//print render($page['content']);
$block = block_load('views', 'careers-block_3');
//var_dump($block);
$output = drupal_render(_block_get_renderable_array(_block_render_blocks(array($block))));
print $output; ?>
</body>