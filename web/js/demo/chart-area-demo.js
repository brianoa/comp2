document.addEventListener("DOMContentLoaded", function () {
  // Helper function to format numbers with commas and two decimal places
  function formatNumber(num) {
    return num.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  // Fetch daily revenue data
  var dailyDataElem = document.getElementById("dailyRevenueData");
  var dailyLabels = JSON.parse(dailyDataElem.getAttribute("data-labels"));
  var dailyData = JSON.parse(dailyDataElem.getAttribute("data-data"));

  // Format daily revenue data
  dailyData = dailyData.map(formatNumber);

  // Set new default font family and font color to mimic Bootstrap's default styling
  Chart.defaults.global.defaultFontFamily =
    '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
  Chart.defaults.global.defaultFontColor = "#292b2c";

  // Daily Revenue Chart
  var dailyCtx = document.getElementById("dailyRevenueChart").getContext("2d");
  var dailyChart = new Chart(dailyCtx, {
    type: "line",
    data: {
      labels: dailyLabels,
      datasets: [
        {
          label: "Total Revenue",
          lineTension: 0.3,
          backgroundColor: "rgba(2,117,216,0.2)",
          borderColor: "rgba(2,117,216,1)",
          pointRadius: 5,
          pointBackgroundColor: "rgba(2,117,216,1)",
          pointBorderColor: "rgba(255,255,255,0.8)",
          pointHoverRadius: 5,
          pointHoverBackgroundColor: "rgba(2,117,216,1)",
          pointHitRadius: 50,
          pointBorderWidth: 2,
          data: dailyData,
        },
      ],
    },
    options: {
      scales: {
        xAxes: [
          {
            time: {
              unit: "date",
            },
            gridLines: {
              display: false,
            },
            ticks: {
              maxTicksLimit: 7,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              min: 0,
              maxTicksLimit: 5,
              callback: function (value) {
                return formatNumber(value);
              },
            },
            gridLines: {
              color: "rgba(0, 0, 0, .125)",
            },
          },
        ],
      },
      legend: {
        display: false,
      },
      tooltips: {
        callbacks: {
          label: function (tooltipItem, data) {
            return formatNumber(tooltipItem.yLabel);
          },
        },
      },
    },
  });
});
