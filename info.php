<html>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="http://code.highcharts.com/modules/data.js"></script>
    <script src="http://code.highcharts.com/modules/exporting.js"></script>
    <script src="WEB-INF/js/GraphViews.js"></script>

</html>
<body>
 <div id="containerSignUps" style="width:100%; height:400px;"></div>
 <div id="containerUserVisits" style="width:100%; height:400px;"></div>
 <div id="containerRetentionRate" style="width:100%; height:400px;"></div>
 <div id="containerUserBreakDown" style="width:100%; height:400px;"></div>
 <?php
  $graph1Data = array();
  $graph2Data = array();
  $graph3Data = array();
  $graph4Data = array();

  include "GraphData.php";
 ?>
  <script>
      createPerWeekGraph(<?php echo $graph1Data; ?>);
      createUserVistsPerWeekGraph(<?php echo $graph2Data; ?>);
      createRetentionUserRateGraph(<?php echo $graph3Data; ?>);
      createStackedUpGraph(<?php echo $graph4Data; ?>);

  </script>
</body>


