let socket;
$(function () {
    socket = io('http://10.100.3.20:2020');
    socket.on('connect', function () {
        console.log('connected');
    });
    socket.on('updateResult', function (rows) {
        // let data = [[null, 'Итого']];
        // console.log(rows['results']);
        // if (rows['results']) {
        //     $.each(rows['results'], function () {
        //         data.push([this.name, this.data]);
        //     });
        //     drawChart(data);
        // }
    });
    socket.emit('answer');
    initChart();
});

// function drawChart(data) {
//     if (!$("#chart").data('highchartsChart')) {
//         initChart(data);
//     } else {
//         chart.series[0].addPoint(data, true);
//     }
// }

function initChart() {
    window.chart = Highcharts.chart('chart', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Приоритет специальностей'
        },
        xAxis: {
            type: 'category',
            labels: {
                style: {
                    fontSize: '48px'
                }
            }
        },
        yAxis: {
            title: {
                text: 'Кол-во'
            }
        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y}',
                    style: {
                        fontSize: '20px'
                    }
                }
            }
        },
        data: {
            csvURL: window.location.origin + '/get-statistic',
            enablePolling: true,
            dataRefreshRate: 1
        },
        series: [
            {
                "name": "Итог",
                "colorByPoint": true,
                "data": []
            }
        ]
    })
}