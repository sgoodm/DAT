<!DOCTYPE html>
<html> 

<head>
   <meta charset="UTF-8">
   <title>Data Access Tool</title> 

   <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
   <link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
   <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
   <link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300' rel='stylesheet' type='text/css'>
   <link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>

   <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" />

   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" />

   <link rel="stylesheet" href="index.css?<?php echo filectime('index.css') ?>" />   

</head>

<body class="container">

   <div id="header" class="row">
      Data Access Tool
   </div>

   <div id="message">please select a country</div>


   <div id="load" class="row">
      <span> Select a Country: </span>
      <select id="country" >
         <option id="blank_country_option" value="-----" class="blank">-----</option>
        <!--  <option value="Nepal">Nepal</option>
         <option value="Uganda">Uganda</option>
         <option value="Malawi">Malawi</option>  -->
         <option value="Senegal">Senegal</option>  
         <option value="Timor-Leste">Timor-Leste</option>  

      </select>
      <button>Load Options</button>
   </div>

   <div>

      <div id="transaction_radio_container">
         <label>Commitments<input type="radio" name="transaction_type" value="C" checked></label>
         <label>Disbursements<input type="radio" name="transaction_type" value="D"></label>
      </div>

      <div id="slider_container">
         <div id="slider_top" class="slider_sub">
            <div id="slider"></div>
         </div> 
         <div id="slider_bot" class="slider_sub">  
            <span id="slider_min"></span>
            <span id="slider_value"></span>
            <span id="slider_max"></span>
         </div>
      </div>

   </div>

   <div id="options" class="row">

      <div id="optcol1" class="col-md-3">
            <span> Aggregation Field </span>
            <!-- <span id="det_link">(to aggregate by geography use <a href="/aiddata/DET/www/det.php">THIS</a> tool)</span> -->
            <select id="aggregate" class="aggregate">
               <option value="-----" class="blank">please select a country</option>
            </select>

            <span> Sub Aggregation Field </span>
            <select id="subaggregate" class="aggregate" disabled>
               <option value="-----" class="blank">please select a country</option>
            </select>

            <span> Geo Aggregation Level </span>
            <select id="geoaggregate" class="aggregate" disabled>
               <option value="1">adm1</option>
               <option value="2">adm2</option>
            </select>

            <span> Precision Codes </span>
            <div id="precision" >
               <label class="option"><input class="precision_op" type="checkbox" value="1" checked disabled>1</label>
               <label class="option"><input class="precision_op" type="checkbox" value="2" checked disabled>2</label>
               <label class="option"><input class="precision_op" type="checkbox" value="3" checked disabled>3</label>
               <label class="option"><input class="precision_op" type="checkbox" value="4" checked disabled>4</label>
               <label class="option"><input class="precision_op" type="checkbox" value="5" checked disabled>5</label>
               <label class="option"><input class="precision_op" type="checkbox" value="6" checked disabled>6</label>
               <label class="option"><input class="precision_op" type="checkbox" value="7" checked disabled>7</label>
               <label class="option"><input class="precision_op" type="checkbox" value="8" checked disabled>8</label>
               <label class="option"><input class="precision_op" type="checkbox" value="9" checked disabled>9</label>
            </div>
      </div>

      <div id="optcol2" class="col-md-3">
         <span> Filter Field </span>
         <div id="filter" >
            <label class="blank"><input type="checkbox" value="-----">please select a country</label>
         </div>
      </div>

      <div id="optcol3" class="col-md-6">
         <span> Filter Options </span>
         <div id="filter_radio_container">
            <label>Or<input type="radio" name="filter_type" value="or" checked></label>
            <label>And<input type="radio" name="filter_type" value="and"></label>
         </div>
         <div id="values" >
            <span value="-----" class="blank group">please select a country</span>
         </div>
      </div>

   </div>

   <div id="export" class="row">
      <button id="submit">Generate CSV</button>
   </div>

   <div id="download"></div>

   <div id="info">
      <div>
         <h3>Tips</h3>
         <ul>
            <li>If you want the raw data but just a specific subset of the Release, leave the <b>Aggregation Field</b> as <i>None</i> and use the <b>Filter Field</b> and <b>Filter Options</b> to select the data you want.</li>
            <li>To filter by date, use the slider to select a range.</li>
            <!-- <li>Tip</li> -->
         </ul>
      </div>
      <div>
         <h3>Info</h3>
         <ul>
            <li>Using the <b>Aggregation Field</b> option will only return fields that can be aggregated (normally only project commitments and disbursments)</li>
            <li>When filtering by date, the total commitments and disbursements will be given for any project active within the range along with the commitments or disbursements for the selected range.</li>
            <!-- <li>Info</li> -->
         </ul>
      </div>
   </div>

    <div id="navbar_spacer"></div>
    <?php include("/var/www/html/aiddata/home/nav.php"); ?>  



<!--==================================================================================================== -->


   <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

   <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>

   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

   <script src="/aiddata/libs/dragslider.js"></script>

   <script src="/aiddata/libs/underscoremin.js"></script>

   <script src="index.js"></script> 

</body>

</html>