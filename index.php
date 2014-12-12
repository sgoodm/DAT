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

   <div id="load" class="row">
      <span> Select a Country: </span>
      <select id="country" >
         <option id="blank_country_option" value="-----">-----</option>
         <option value="Nepal">Nepal</option>
         <option value="Uganda">Uganda</option>
         <option value="Malawi">Malawi</option> 
         <option value="Senegal">Senegal</option>  
      </select>
      <button>Load Options</button>
   </div>

   <div id="options" class="row">

      <div id="optcol1" class="col-md-4">
            <span> Aggregation Field </span>
            <select id="aggregate" size="5">
               <option value="-----">please select a country</option>
            </select>
      </div>

      <div id="optcol2" class="col-md-4">
         <span> Filter Field </span>
         <div id="filter" >
            <label><input type="checkbox" value="-----">please select a country</label>
         </div>
      </div>

      <div id="optcol3" class="col-md-4">
         <span> Filter Value </span>
         <div id="values" >
            <span value="-----">please select a country</span>
         </div>
      </div>

   </div>

   <div id="export" class="row">
      <button id="submit">Generate CSV</button>
   </div>

   <div id="info">
      <div>
         <h3>Tips</h3>
         <ul>
            <li>If you want the raw data but just a specific subset of the Release, leave the <b>Aggregation Field</b> as <i>None</i> and use the <b>Filter Field</b> and <b>Filter Value</b> to select the data you want.</li>
            <!-- <li>Tip</li> -->
         </ul>
      </div>
      <div>
         <h3>Info</h3>
         <ul>
            <li>Using the <b>Aggregation Field</b> option will only return fields that can be aggregated (normally only project commitments and disbursments)</li>
            <!-- <li>Info</li> -->
         </ul>
      </div>
   </div>


<!--==================================================================================================== -->


   <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

   <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>

   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

   <script src="libs/jquery.csv-0.71.min.js"></script>

   <script src="/aiddata/libs/underscoremin.js"></script>

   <script src="index.js"></script> 

</body>

</html>