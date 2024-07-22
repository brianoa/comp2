document.addEventListener("DOMContentLoaded", function () {
  // Helper function to format numbers with commas and two decimal places
  function formatNumber(num) {
    return num.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  // Get the chart data from HTML
  var chartDataElement = document.getElementById("chartData");
  var months = JSON.parse(chartDataElement.getAttribute("data-months"));
  var revenues = JSON.parse(chartDataElement.getAttribute("data-revenues"));

  // Format revenues data
  revenues = revenues.map(formatNumber);

  console.log("LABELS", months);
  console.log("REVENUES", revenues);

  // Set new default font family and font color to mimic Bootstrap's default styling
  Chart.defaults.global.defaultFontFamily =
    '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
  Chart.defaults.global.defaultFontColor = "#292b2c";

  // Bar Chart Example
  var ctx = document.getElementById("myBarChart").getContext("2d");
  var myBarChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: months, // Use months from HTML
      datasets: [
        {
          label: "Revenue",
          backgroundColor: "rgba(2,117,216,1)",
          borderColor: "rgba(2,117,216,1)",
          data: revenues, // Use revenues from HTML
        },
      ],
    },
    options: {
      scales: {
        xAxes: [
          {
            gridLines: {
              display: false,
            },
            ticks: {
              maxTicksLimit: 6,
            },
          },
        ],
        yAxes: [
          {
            ticks: {
              min: 0,
              max: Math.max(...revenues.map(Number)) + 1000, // Dynamic max value
              maxTicksLimit: 5,
              callback: function (value) {
                return formatNumber(value);
              },
            },
            gridLines: {
              display: true,
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
