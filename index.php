<!DOCTYPE html>
<html lang="en">
<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>ESP Flood Detection</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <style>
    	
    	@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

		*,
		::after,
		::before {
		    box-sizing: border-box;
		    padding: 0;
		    margin: 0;
		}

		a {
		    text-decoration: none;
		}

		li {
		    list-style: none;
		}

		body {
		    font-family: 'Poppins', sans-serif;
            height: 500px;
            margin: 0 auto;
		}

		h2 {
	      font-family: Arial;
	      font-size: 2.5rem;
	      text-align: center;
	    }

		.container {
		    display: flex;
		}

		.wrapper {
			display: flex;
		}

		.main {
			min-height: 100vh;
			width: 100%;
			overflow: hidden;
			transition: all 0.35s ease-in-out;
			background-color: #fafbfe;
		}

		#sidebar {
			width: 70px;
			min-width: 70px;
			z-index: 1000;
			transition: all .25s ease-in-out;
			display: flex;
			flex-direction: column;
			background-color: #0e2238;
		}

		#sidebar.expand {
			width: 260px;
			min-width: 260px;
		}

		#toggler {
		    display: none;
		}

		.toggle-btn {
		    font-size: 1.5rem;
		    cursor: pointer;
		    color: #FFF;
		    padding: 1rem 1.5rem;
		    width: max-content;
		}

		#toggle-btn {
			background-color: transparent;
			cursor: pointer;
			border: 0;
			padding: 1rem 1.5rem;
		}

		#toggle-btn i {
			font-size: 1.5rem;
			color: #fff;
		}

		.sidebar-logo {
			margin: auto 0;
		}

		.sidebar-logo a {
			color: #fff;
			font-size: 1.15rem;
			font-weight: 600;
		}

		#sidebar:not(.expand) .sidebar-logo,
		#sidebar:not(.expand) a.sidebar-link span {
			display: none;
		}

		.sidebar-nav {
			padding: 2rem 0;
			flex: 1 1 auto;
		}

		a.sidebar-link {
			padding: .625rem 1.625rem;
			color: #FFF;
			display: block;
			font-size: 0.9rem;
			white-space: nowrap;
			border-left: 3px solid transparent;
		}

		.sidebar-link i {
			font-size: 1.1rem;
			margin-right: .75rem;
		}

		a.sidebar-link:hover {
			background-color: rgba(255,255,255,.075);
			border-left: 3px solid #3b7ddd;
		}

		.sidebar-item {
			position: relative;
		}

		#sidebar:not(.expand) .sidebar-item .sidebar-dropdown{
			position: absolute;
			top: 0;
			left: 70px;
			background-color: 0e2238;
			padding: 0;
			min-width: 15rem;
			display: none;
		}	

		#sidebar:not(.expand) .sidebar-item:hover .has-dropdown+.sidebar-dropdown{
			display: block;
			max-height: 15rem;
			width: 100%;
			opacity: 1;
		}

		#sidebar.expand .sidebar-link[data-bs-toggle="collapse"]::after{
			border: solid;
			border-width: 0 .075rem .075rem 0;
			content: "";
			display: inline-block;
			padding: 2px;
			position: absolute;
			right: 1.5rem;
			top: 1.4rem;
			transform: rotate(-135deg);
			transition: all .2s ease-out;
		}

		#sidebar.expand .sidebar-link[data-bs-toggle="collapse"].collapsed::after{
			transform: rotation(45deg);
			transition: all .2s ease-out;
		}

		#sidebar input[type="checkbox"]:checked~.sidebar-nav {
		    width: 260px;
		    min-width: 260px;
		}

		#sidebar input[type="checkbox"]:not(:checked)~* .sidebar-link span {
		    display: none;
		}
		
		th {
            cursor: pointer;
        }

        .pagination {
            display: flex;
            justify-content: center;
        }

        .pagination li {
            list-style-type: none;
            margin: 0 5px;
        }

        .pagination a {
            color: #000;
            text-decoration: none;
        }
        
        .modal-xl {
        max-width: 95% !important;
        }

        #current-status-chart {
		  margin-top: 2rem;
		}

    </style>

</head>

<body>

<?php
$servername = "zen";
$dbname = "mexazeco_esp_data";
$username = "mexazeco_Mexaze";
$password = "@Z33m2011";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the total number of records
$total_sql = "SELECT COUNT(*) FROM SensorData";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_row();
$total_records = $total_row[0];

// Define how many results you want per page
$results_per_page = 10;

// Determine the total number of pages available
$total_pages = ceil($total_records / $results_per_page);

// Find out the current page number
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

// Calculate the starting limit number
$start_limit = ($page - 1) * $results_per_page;

$sql = "SELECT id, sensor, location, humvalue, temvalue, disvalue, watervalue, reading_time FROM SensorData ORDER BY id DESC LIMIT " . $start_limit . ", " . $results_per_page;

$result = $conn->query($sql);

$sensor_data = []; // Initialize the array

while ($data = $result->fetch_assoc()){
    // Adjust reading_time and format it
    $data['reading_time'] = date("h:iA l d-m-Y", strtotime($data['reading_time'] ));
    $sensor_data[] = $data;
}

$readings_time = array_column($sensor_data, 'reading_time');

$humvalue = json_encode(array_reverse(array_column($sensor_data, 'humvalue')), JSON_NUMERIC_CHECK);
$temvalue = json_encode(array_reverse(array_column($sensor_data, 'temvalue')), JSON_NUMERIC_CHECK);
$disvalue = json_encode(array_reverse(array_column($sensor_data, 'disvalue')), JSON_NUMERIC_CHECK);
$reading_time = json_encode(array_reverse($readings_time)); // No need for JSON_NUMERIC_CHECK here
$watervalue = json_encode(array_reverse(array_column($sensor_data, 'watervalue')), JSON_NUMERIC_CHECK);

?>

	<div class="wrapper">
		<aside id=sidebar>
			<div class="d-flex">
				<button id="toggle-btn">
					<i class="bi bi-grid"></i>
				</button>
				<div class="sidebar-logo">
					<a href="#">ESP32 Flood Detection</a>
				</div>
			</div>
			<ul class="sidebar-nav">
				<li class="sidebar-item">
					<a href="#" class="sidebar-link" data-target="home-container">
						<i class="bi bi-house-door"></i>
						<span>Home</span>
					</a>
				</li>
				<li class="sidebar-item">
					<a href="#" class="sidebar-link" data-target="temperature-container">
						<i class="bi bi-thermometer-half"></i>
						<span>Temperature Data</span>
					</a>
				</li>
				<li class="sidebar-item">
					<a href="#" class="sidebar-link" data-target="humidity-container">
						<i class="bi bi-moisture"></i>
						<span>Humidity Data</span>
					</a>
				</li>
				<li class="sidebar-item">
					<a href="#" class="sidebar-link" data-target="distance-container">
						<i class="bi bi-binoculars"></i>
						<span>Distance Data</span>
					</a>
				</li>
				<li class="sidebar-item">
					<a href="#" class="sidebar-link" data-target="wetness-container">
						<i class="bi bi-water"></i>
						<span>Wetness Data</span>
					</a>
				</li>
			</ul>
		</aside>
		<div class="main p-3">
			
			<!-- Home Page -->
			<div id="home-container" class="content-container">
				<div class="text-center">

							<div class="col-lg-10 mx-auto">
					            <div class="card rounded shadow border-0">
					                <div class="card-body p-5 bg-white rounded">
					                    <div class="table-responsive">
											<div class="card mt-4">
											  <div class="card-body">
											    <div id="current-status-chart" style="height: 400px;"></div>
											  </div>
											</div>
										</div>
									</div>
								</div>
							</div>

					<div class="container py-5">

					        <div class="col-lg-10 mx-auto">
					            <div class="card rounded shadow border-0">
					                <div class="card-body p-5 bg-white rounded">
					                    <div class="table-responsive">
					                        <table id="example" style="width:100%" class="table table-striped table-bordered table-hover">
					                            <thead>
					                                <tr>
					                                    <th onclick="sortTable(0)">ID</th>
					                                    <th onclick="sortTable(1)">Sensor</th>
					                                    <th onclick="sortTable(2)">Location</th>
					                                    <th onclick="sortTable(3)">Humidity</th>
					                                    <th onclick="sortTable(4)">Temperature</th>
					                                    <th onclick="sortTable(5)">Distance</th>
					                                    <th onclick="sortTable(6)">Water Level</th>
					                                    <th onclick="sortTable(7)">Timestamp</th>
					                                </tr>
					                            </thead>
					                            <tbody>
					                                <?php
					                                if ($result = $conn->query($sql)) {
					                                    while ($row = $result->fetch_assoc()) {
					                                        $row_id = htmlspecialchars($row["id"]);
					                                        $row_sensor = htmlspecialchars($row["sensor"]);
					                                        $row_location = htmlspecialchars($row["location"]);
					                                        $row_humvalue = htmlspecialchars(number_format($row["humvalue"], 2));
					                                        $row_temvalue = htmlspecialchars(number_format($row["temvalue"], 2));
					                                        $row_disvalue = htmlspecialchars(number_format($row["disvalue"], 2));
					                                        $row_watervalue = htmlspecialchars(number_format($row["watervalue"], 2));
					                                        $row_reading_time = date("h:iA l d-m-Y", strtotime($row["reading_time"] . " +8 hours"));

					                                        echo "<tr>
					                                            <td>{$row_id}</td>
					                                            <td>{$row_sensor}</td>
					                                            <td>{$row_location}</td>
					                                            <td>Humidity = {$row_humvalue} %</td>
					                                            <td>Temperature = {$row_temvalue} °C</td>
					                                            <td>Distance = {$row_disvalue} cm</td>
					                                            <td>Water Level = {$row_watervalue} %</td>
					                                            <td>{$row_reading_time}</td>
					                                        </tr>";
					                                    }
					                                    $result->free();
					                                }
					                                $conn->close();
					                                ?>
					                            </tbody>
					                        </table>
					                    </div>

					                    <nav aria-label="Page navigation example">
					                        <ul class="pagination justify-content-center">
					                            <?php
					                            // Previous Button
					                            if ($page > 1) {
					                                echo '<li class="page-item"><a class="page-link" href="index.php?page=' . ($page - 1) . '">Previous</a></li>';
					                            }

					                            // Display the links to the pages
					                            for ($p = 1; $p <= $total_pages; $p++) {
					                                if ($p == $page) {
					                                    echo '<li class="page-item active"><a class="page-link" href="#">' . $p . '</a></li>';
					                                } else {
					                                    echo '<li class="page-item"><a class="page-link" href="index.php?page=' . $p . '">' . $p . '</a></li>';
					                                }
					                            }

					                            // Next Button
					                            if ($page < $total_pages) {
					                                echo '<li class="page-item"><a class="page-link" href="index.php?page=' . ($page + 1) . '">Next</a></li>';
					                            }
					                            ?>
					                        </ul>
					                    </nav>
					                </div>
					            </div>
					        </div>

					</div>
				</div>
			</div>
			<!-- Home Page -->

			<!-- Temperature Page -->
			<div id="temperature-container" class="content-container d-none">
				<div class="container py-5">

					    <div class="col-lg-10 mx-auto">
					         <div class="card rounded shadow border-0">
					             <div class="card-body p-5 bg-white rounded">
					             	<div id="chart-temperature" class="container"></div>
					             </div>
					         </div>
					     </div>

				</div>
		    </div>
			<!-- Temperature Page -->

			<!-- Humidity Page -->
			<div id="humidity-container" class="content-container d-none">
				<div class="container py-5">

					    <div class="col-lg-10 mx-auto">
					         <div class="card rounded shadow border-0">
					             <div class="card-body p-5 bg-white rounded">
					             	<div id="chart-humidity" class="container"></div>
					             </div>
					         </div>
					     </div>

				</div>
		    </div>
			<!-- Humidity Page -->

			<!-- Distance Page -->
			<div id="distance-container" class="content-container d-none">
				<div class="container py-5">

					    <div class="col-lg-10 mx-auto">
					         <div class="card rounded shadow border-0">
					             <div class="card-body p-5 bg-white rounded">
					             	<div id="chart-pressure" class="container"></div>
					             </div>
					         </div>
					     </div>

				</div>
		    </div>
			<!-- Distance Page -->

			<!-- Wetness Page -->
			<div id="wetness-container" class="content-container d-none">
				<div class="container py-5">

					    <div class="col-lg-10 mx-auto">
					         <div class="card rounded shadow border-0">
					             <div class="card-body p-5 bg-white rounded">
					             	<div id="chart-wetness" class="container"></div>
					             </div>
					         </div>
					     </div>

				</div>
		    </div>
			<!-- Wetness Page -->

		</div>
	</div>
    
	<!-- JavaScript for Table Sorting -->
	<script>
	    function sortTable(n) {
	        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	        table = document.getElementById("example");
	        switching = true;
	        dir = "asc"; // Set the sorting direction to ascending

	        while (switching) {
	            switching = false;
	            rows = table.rows;
	            for (i = 1; i < (rows.length - 1); i++) {
	                shouldSwitch = false;
	                x = rows[i].getElementsByTagName("TD")[n];
	                y = rows[i + 1].getElementsByTagName("TD")[n];

	                // Compare based on direction
	                if (dir == "asc") {
	                    if (x.innerText.toLowerCase() > y.innerText.toLowerCase()) {
	                        shouldSwitch = true;
	                        break;
	                    }
	                } else if (dir == "desc") {
	                    if (x.innerText.toLowerCase() < y.innerText.toLowerCase()) {
	                        shouldSwitch = true;
	                        break;
	                    }
	                }
	            }
	            if (shouldSwitch) {
	                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
	                switching = true;
	                switchcount++;
	            } else {
	                // If no switching has been done AND direction is "asc", switch to "desc"
	                if (switchcount == 0 && dir == "asc") {
	                    dir = "desc";
	                    switching = true;
	                }
	            }
	        }
	    }
	</script>

	<!-- Chart Container -->
	<script>

	var humvalue = <?php echo $humvalue; ?>;
	var temvalue = <?php echo $temvalue; ?>;
	var disvalue = <?php echo $disvalue; ?>;
	var watervalue = <?php echo $watervalue; ?>;
	var reading_time = <?php echo $reading_time; ?>;

	var chartT = new Highcharts.Chart({
	  chart:{ renderTo : 'chart-temperature' },
	  title: { text: 'ESP32 Temperature' },
	  series: [{
	    showInLegend: false,
	    data: temvalue // Corrected variable name
	  }],
	  plotOptions: {
	    line: { animation: false,
	      dataLabels: { enabled: true }
	    },
	    series: { color: '#059e8a' }
	  },
	  xAxis: { 
	    type: 'datetime',
	    categories: reading_time
	  },
	  yAxis: {
	    title: { text: 'Temperature (Celsius)' }
	    //title: { text: 'Temperature (Fahrenheit)' }
	  },
	  credits: { enabled: false }
	});

	var chartH = new Highcharts.Chart({
	  chart:{ renderTo:'chart-humidity' },
	  title: { text: 'ESP32 Humidity' },
	  series: [{
	    showInLegend: false,
	    data: humvalue // Corrected variable name
	  }],
	  plotOptions: {
	    line: { animation: false,
	      dataLabels: { enabled: true }
	    }
	  },
	  xAxis: {
	    type: 'datetime',
	    //dateTimeLabelFormats: { second: '%H:%M:%S' },
	    categories: reading_time
	  },
	  yAxis: {
	    title: { text: 'Humidity (%)' }
	  },
	  credits: { enabled: false }
	});

	var chartW = new Highcharts.Chart({
	  chart:{ renderTo:'chart-wetness' },
	  title: { text: 'ESP32 Wetness' },
	  series: [{
	    showInLegend: false,
	    data: watervalue // Corrected variable name
	  }],
	  plotOptions: {
	    line: { animation: false,
	      dataLabels: { enabled: true }
	    }
	  },
	  xAxis: {
	    type: 'datetime',
	    //dateTimeLabelFormats: { second: '%H:%M:%S' },
	    categories: reading_time
	  },
	  yAxis: {
	    title: { text: 'Wetness (%)' }
	  },
	  credits: { enabled: false }
	});

	var chartP = new Highcharts.Chart({
	  chart:{ renderTo:'chart-pressure' },
	  title: { text: 'ESP32 Distance' },
	  series: [{
	    showInLegend: false,
	    data: disvalue // Corrected variable name
	  }],
	  plotOptions: {
	    line: { animation: false,
	      dataLabels: { enabled: true }
	    },
	    series: { color: '#18009c' }
	  },
	  xAxis: {
	    type: 'datetime',
	    categories: reading_time
	  },
	  yAxis: {
	    title: { text: 'Distance (cM)' }
	  },
	  credits: { enabled: false }
	});

	</script>



    <!-- Bootstrap JS Bundle (includes Popper) -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YpC+nAAM+1JlVkP3y1VZ4zqT3BfvzQ14uGHljPPV6vOMV3B9OQxWv0FdADtI/5hI" crossorigin="anonymous"></script>

	<script>
		
		const hamburger = document.querySelector("#toggle-btn");

		hamburger.addEventListener("click", function () {
			document.querySelector("#sidebar").classList.toggle("expand");
		});

	</script>

	<!-- Current Status charge -->
	<script>
	  const latestTemp = temvalue[temvalue.length - 1];
	  const latestHum = humvalue[humvalue.length - 1];
	  const latestDist = disvalue[disvalue.length - 1];
	  const latestWater = watervalue[watervalue.length - 1];

	  const chartStatus = Highcharts.chart('current-status-chart', {
	    chart: {
	      type: 'column'
	    },
	    title: {
	      text: 'Live Flood Detection Status'
	    },
	    xAxis: {
	      categories: ['Temperature (Celcius)', 'Humidity (%)', 'Distance (cm)', 'Water Level (%)'],
	      crosshair: true
	    },
	    yAxis: {
	      min: 0,
	      title: {
	        text: 'Sensor Value'
	      }
	    },
	    tooltip: {
	      shared: true
	    },
	    plotOptions: {
	      column: {
	        dataLabels: {
	          enabled: true
	        },
	        colorByPoint: true
	      }
	    },
	    series: [{
	      name: 'Latest Reading',
	      data: [
	        {
	          y: latestTemp,
	          color: getColor('temperature', latestTemp)
	        },
	        {
	          y: latestHum,
	          color: getColor('humidity', latestHum)
	        },
	        {
	          y: latestDist,
	          color: getColor('distance', latestDist)
	        },
	        {
	          y: latestWater,
	          color: getColor('water', latestWater)
	        }
	      ]
	    }]
	  });

	  function getColor(sensor, value) {
	    switch (sensor) {
	      case 'temperature':
	        if (value < 30) return 'green';
	        if (value < 35) return 'yellow';
	        return 'red';
	      case 'humidity':
	        if (value < 60) return 'green';
	        if (value < 80) return 'yellow';
	        return 'red';
	      case 'distance': // assume < 10 cm is critical
	        if (value > 20) return 'green';
	        if (value > 10) return 'yellow';
	        return 'red';
	      case 'water':
	        if (value < 30) return 'green';
	        if (value < 60) return 'yellow';
	        return 'red';
	    }
	  }
	</script>


	<!-- Container change -->
	<script>
		document.querySelectorAll('.sidebar-link').forEach(link => {
		    link.addEventListener('click', function (e) {
		        e.preventDefault();
		        const targetId = this.getAttribute('data-target');
		        const containers = document.querySelectorAll('.content-container');

		        containers.forEach(container => {
		            if (container.id === targetId) {
		                container.classList.remove('d-none');
		            } else {
		                container.classList.add('d-none');
		            }
		        });
		    });
		});
	</script>

</body>
</html>