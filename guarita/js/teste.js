

			// Load the Visualization API and the piechart package.
			google.load('visualization', '1', {'packages':['corechart']});

			// Set a callback to run when the Google Visualization API is loaded.
			google.setOnLoadCallback(drawChart);

			function drawChart() {
				var json = $.ajax({
					url: 'get_json.php', // make this url point to the data file
					dataType: 'json',
					async: false
				}).responseText;
				
				// Create our data table out of JSON data loaded from server.
				var data = new google.visualization.DataTable(json);
				var options = {
					title: 'Passagem Geral',
					is3D: 'true',
					width: 400,
					height: 300
				};
				// Instantiate and draw our chart, passing in some options.
				//do not forget to check ur div ID
				var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
				chart.draw(data, options);

				setInterval(drawChart, 500 );
			}
		