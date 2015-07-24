function createPerWeekGraph(data) {
	var signups = [];
	for (key in data) {
		if (data.hasOwnProperty(key)) {
			signups.push(parseInt(data[key].signups));
		}
	}
	var chart = new Highcharts.Chart({
		chart: {
			renderTo: 'containerSignUps',
			type: 'column'
		},
		title: {
			text: 'Rate of New Signups per week'
		},
		tooltip: {
			formatter: function () {
			   return 'Rate of New Signup for Week <b>' + this.x + '</b> is <b>' + this.y + '</b>';
			}
		},
		xAxis: {
			title: {
				text: 'Weeks'
			},
			labels: {
				formatter: function () {
					return "Week :" + this.value;
				}
			},
			tickInterval: 1
		},
		yAxis: {
			title: {
				text: 'Signup Counts'
			},
			tickInterval: 5

		},
		series: [{
			name: 'Signup count per week',
			data: signups
		}]
	});
}

function createUserVistsPerWeekGraph(data) {
	var userIds = [];
	console.info(data);
	for (key in data) {
		if (data.hasOwnProperty(key)) {
			userIds.push(parseInt(data[key].userIds));
		}
	}
	console.info(userIds);
	var chart = new Highcharts.Chart({
		chart: {
			renderTo: 'containerUserVisits',
			type: 'column'
		},
		title: {
			text: 'User Visits per week'
		},
		tooltip: {
			formatter: function () {
				return 'User Visit for Week <b>' + this.x + '</b> is <b>' + this.y + '</b>';
			}
		},
		xAxis: {
			title: {
				text: 'Weeks'
			},
			labels: {
				formatter: function () {
					return "Week :" + this.value;
				}
			},
			tickInterval: 1
		},
		yAxis: {
			title: {
				text: 'User Visits'
			},
			tickInterval: 5

		},
		series: [{
			name: 'User Visits per week',
			data: userIds
		}]
	});
}


function createRetentionUserRateGraph(data) {
	var possible = [], actual = [], retentionRate = [];

	for (key in data) {
		if (data.hasOwnProperty(key)) {
			possible.push(parseInt(data[key].possible));
			actual.push(parseInt(data[key].actual));
			retentionRate.push(parseFloat((parseFloat(data[key].retentionrate) * 100).toFixed(2)));
		}
	}

	var chart = new Highcharts.Chart({
		chart: {
			renderTo: 'containerRetentionRate',
			type: 'column'
		},
		title: {
			text: 'Retention Rate by week age of User'
		},
		xAxis: {
			title: {
				text: 'Weeks'
			},
			labels: {
				formatter: function () {
					return "Week :" + this.value;
				}
			},
			tickInterval: 1
		},
		yAxis: {
			title: {
				text: 'Visits'
			},
			tickInterval: 1

		},
		tooltip: {
			shared: true,
			crosshairs: true,
			formatter: function () {
				return 'The Retention Rate for Week <b>' + this.x + '</b> is <b>' + this.y + '</b>%';
			}
		},

		series: [
			//{
			//	name: 'Possible Visits',
			//	data: possible
			//},
			//	{
			//	name: 'Actual Visits',
			//	data: actual
			//},
			{
				name: 'Retention Rate in %',
				data: retentionRate
			}
		]
	});
}

function createStackedUpGraph(data) {
	var signups = [], retained = [], resurrected = [], churned = [];
	var category = ['signups', 'retained', 'resurrected', 'churned'];

	for (key in data) {
		if (data.hasOwnProperty(key)) {
			signups.push(parseInt(data[key].signups));
			retained.push(parseInt(data[key].retained));
			churned.push(parseInt(data[key].churned));
			resurrected.push(parseInt(data[key].resurrected));
		}
	}

	var chart = new Highcharts.Chart({
		chart: {
			type: 'column',
			renderTo: 'containerUserBreakDown'
		},
		title: {
			text: 'User Break Down'
		},
		xAxis: {
			title: {
				text: 'Weeks'
			},
			labels: {
				formatter: function () {
					return "Week :" + this.value;
				}
			},
			tickInterval: 1
		},
		yAxis: {
			min: 0,
			title: {
				text: 'User Break Down'
			},
			stackLabels: {
				enabled: true,
				style: {
					fontWeight: 'bold',
					color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
				}
			}
		},
		legend: {
			align: 'right',
			x: -30,
			verticalAlign: 'top',
			y: 25,
			floating: true,
			backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
			borderColor: '#CCC',
			borderWidth: 1,
			shadow: false
		},
		tooltip: {
			formatter: function () {
				return 'User Break down for Week <b>' + this.x + '</b><br/>' + this.series.name + ': '+ this.y + '<br/>';
			}
		},
		plotOptions: {
			column: {
				stacking: 'normal',
				dataLabels: {
					enabled: true,
					color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
					style: {
						textShadow: '0 0 3px black'
					}
				}
			}
		},
		series: [{
			name: 'Signups',
			data: signups
		}, {
			name: 'Retained',
			data: retained
		}, {
			name: 'Churned',
			data: churned
		}, {
			name: 'Resurrected',
			data: resurrected
		}]
	});

}