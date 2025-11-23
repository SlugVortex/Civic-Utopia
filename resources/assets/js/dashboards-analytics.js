/**
 * Dashboards Analytics
 */

'use strict';

(function () {
  let cardColor,
    headingColor,
    labelColor,
    fontFamily,
    borderColor,
    heatMap1,
    heatMap2,
    heatMap3,
    heatMap4,
    bodyColor,
    currentTheme,
    chartBgColor;

  if (isDarkStyle) {
    heatMap1 = '#333457';
    heatMap2 = '#3c3e75';
    heatMap3 = '#484b9b';
    heatMap4 = '#696cff';
    chartBgColor = '#474360';
    currentTheme = 'dark';
  } else {
    heatMap1 = '#ededff';
    heatMap2 = '#d5d6ff';
    heatMap3 = '#b7b9ff';
    heatMap4 = '#696cff';
    chartBgColor = '#F0F2F8';
    currentTheme = 'light';
  }
  cardColor = config.colors.cardColor;
  headingColor = config.colors.headingColor;
  labelColor = config.colors.textMuted;
  borderColor = config.colors.borderColor;
  bodyColor = config.colors.bodyColor;
  fontFamily = config.fontFamily;

  // Chart Colors
  const chartColors = {
    donut: {
      series1: config.colors.primary,
      series2: '#9055fdb3',
      series3: '#9055fd80'
    },
    donut2: {
      series1: '#49AC00',
      series2: '#4DB600',
      series3: config.colors.success,
      series4: '#78D533',
      series5: '#9ADF66',
      series6: '#BBEA99'
    },
    line: {
      series1: config.colors.warning,
      series2: config.colors.primary,
      series3: '#7367f029'
    }
  };

  // Performance Radar Chart
  // --------------------------------------------------------------------
  const performanceChartEl = document.querySelector('#performanceChart'),
    performanceChartConfig = {
      chart: {
        height: 250,
        type: 'radar',
        offsetY: 10,
        toolbar: {
          show: false
        }
      },
      legend: {
        show: true,
        position: 'bottom',
        markers: {
          size: 5,
          width: 10,
          height: 10,
          offsetX: -2,
          strokeWidth: 0
        },
        itemMargin: { horizontal: 10 },
        fontFamily: fontFamily,
        fontSize: '15px',
        labels: {
          colors: labelColor,
          useSeriesColors: false
        }
      },
      plotOptions: {
        radar: {
          polygons: {
            strokeColors: borderColor,
            connectorColors: borderColor
          }
        }
      },
      yaxis: {
        show: false
      },
      series: [
        {
          name: 'Income',
          data: [70, 90, 80, 95, 75, 90]
        },
        {
          name: 'Net Worth',
          data: [110, 78, 95, 85, 95, 78]
        }
      ],
      colors: [config.colors.warning, config.colors.primary],
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        labels: {
          show: true,
          style: {
            colors: [labelColor, labelColor, labelColor, labelColor, labelColor, labelColor],
            fontSize: '13px',
            fontFamily: fontFamily,
            fontWeight: 400
          }
        }
      },
      fill: {
        opacity: [1, 0.9]
      },
      stroke: {
        show: false,
        width: 0
      },
      markers: {
        size: 0
      },
      grid: {
        show: false,
        padding: {
          bottom: -10
        }
      }
    };
  if (typeof performanceChartEl !== undefined && performanceChartEl !== null) {
    const performanceChart = new ApexCharts(performanceChartEl, performanceChartConfig);
    performanceChart.render();
  }

  // Sessions line chart
  // --------------------------------------------------------------------
  const sessionsChartEl = document.querySelector('#sessions'),
    sessionsChartConfig = {
      chart: {
        height: 100,
        type: 'line',
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      grid: {
        borderColor: labelColor,
        strokeDashArray: 6,
        xaxis: {
          lines: {
            show: true
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        },
        padding: {
          top: -15,
          left: -7,
          right: 7,
          bottom: -15
        }
      },
      colors: [config.colors.info],
      stroke: {
        width: 3
      },
      series: [
        {
          data: [0, 20, 5, 30, 15, 45]
        }
      ],
      tooltip: {
        shared: false,
        intersect: true,
        x: {
          show: false
        }
      },
      xaxis: {
        labels: {
          show: false
        },
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: false
        }
      },
      yaxis: {
        labels: {
          show: false
        }
      },
      tooltip: {
        enabled: false
      },
      markers: {
        size: 6,
        strokeWidth: 3,
        strokeColors: 'transparent',
        colors: ['transparent'],
        discrete: [
          {
            seriesIndex: 0,
            dataPointIndex: 5,
            fillColor: cardColor,
            strokeColor: config.colors.info,
            size: 6,
            shape: 'circle'
          }
        ],
        hover: {
          size: 7
        }
      },
      responsive: [
        {
          breakpoint: 768,
          options: {
            chart: {
              height: 110
            }
          }
        }
      ]
    };
  if (typeof sessionsChartEl !== undefined && sessionsChartEl !== null) {
    const sessionsChart = new ApexCharts(sessionsChartEl, sessionsChartConfig);
    sessionsChart.render();
  }

  // Total Transactions Bar Chart
  // --------------------------------------------------------------------
  const totalTransactionChartEl = document.querySelector('#totalTransactionChart'),
    totalTransactionChartConfig = {
      chart: {
        height: 218,
        stacked: true,
        type: 'bar',
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return Math.abs(val);
          }
        }
      },
      legend: { show: false },
      dataLabels: { enabled: false },
      colors: [config.colors.primary, config.colors.success],
      grid: {
        borderColor,
        xaxis: { lines: { show: true } },
        yaxis: { lines: { show: false } },
        padding: {
          top: -5,
          bottom: -25
        }
      },
      states: {
        hover: { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      },
      plotOptions: {
        bar: {
          borderRadius: 5,
          barHeight: '30%',
          horizontal: true,
          endingShape: 'flat',
          startingShape: 'rounded'
        }
      },
      xaxis: {
        position: 'top',
        axisTicks: { show: false },
        axisBorder: { show: false },
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px',
            fontFamily: 'Inter'
          },
          formatter: function (val) {
            return Math.abs(Math.round(val));
          }
        }
      },
      yaxis: { labels: { show: false } },
      series: [
        {
          name: 'Last Week',
          data: [83, 153, 213, 279, 213, 153, 83]
        },
        {
          name: 'This Week',
          data: [-84, -156, -216, -282, -216, -156, -84]
        }
      ]
    };
  if (typeof totalTransactionChartEl !== undefined && totalTransactionChartEl !== null) {
    const totalTransactionChart = new ApexCharts(totalTransactionChartEl, totalTransactionChartConfig);
    totalTransactionChart.render();
  }

  // Total Revenue
  // --------------------------------------------------------------------
  const totalRevenueEl = document.querySelector('#totalRevenue'),
    totalRevenueConfig = {
      chart: {
        height: 115,
        type: 'bar',
        distributed: true,
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      grid: {
        padding: {
          top: -20,
          left: -14,
          right: 0,
          bottom: -15
        },
        yaxis: {
          lines: { show: false }
        }
      },
      series: [
        {
          name: 'Earning',
          data: [120, 200, 150, 120]
        },
        {
          name: 'Expense',
          data: [72, 120, 50, 65]
        }
      ],
      legend: {
        show: false
      },
      tooltip: {
        enabled: false
      },
      dataLabels: {
        enabled: false
      },
      colors: [config.colors.primary, config.colors.warning],
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: '48%',
          startingShape: 'rounded',
          borderRadiusApplication: 'end'
        }
      },
      states: {
        active: {
          filter: {
            type: 'none'
          }
        }
      },
      xaxis: {
        labels: {
          show: false
        },
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: false
        }
      },
      yaxis: {
        labels: {
          show: false
        }
      }
    };
  if (typeof totalRevenueEl !== undefined && totalRevenueEl !== null) {
    const totalRevenue = new ApexCharts(totalRevenueEl, totalRevenueConfig);
    totalRevenue.render();
  }

  // Overview Chart
  // --------------------------------------------------------------------
  const overviewChartEl = document.querySelector('#overviewChart'),
    overviewChartConfig = {
      chart: {
        height: 120,
        type: 'radialBar',
        sparkline: {
          enabled: true
        }
      },
      plotOptions: {
        radialBar: {
          hollow: {
            size: '55%'
          },
          dataLabels: {
            name: {
              show: false
            },
            value: {
              show: true,
              offsetY: 5,
              fontWeight: 500,
              fontSize: '1rem',
              fontFamily: 'Inter',
              color: headingColor
            }
          },
          track: {
            background: config.colors_label.secondary
          }
        }
      },
      states: {
        hover: {
          filter: {
            type: 'none'
          }
        },
        active: {
          filter: {
            type: 'none'
          }
        }
      },
      stroke: {
        lineCap: 'round'
      },
      colors: [config.colors.primary],
      grid: {
        padding: {
          bottom: -15
        }
      },
      series: [64],
      labels: ['Progress']
    };
  if (typeof overviewChartEl !== undefined && overviewChartEl !== null) {
    const overviewChart = new ApexCharts(overviewChartEl, overviewChartConfig);
    overviewChart.render();
  }

  // Sales Country Bar Chart
  // --------------------------------------------------------------------
  const salesCountryChartEl = document.querySelector('#salesCountryChart'),
    salesCountryChartConfig = {
      chart: {
        type: 'bar',
        height: 368,
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: 'Sales',
          data: [17165, 13850, 12375, 9567, 7880]
        }
      ],
      plotOptions: {
        bar: {
          borderRadius: 8,
          barHeight: '60%',
          horizontal: true,
          distributed: true,
          startingShape: 'rounded',
          dataLabels: {
            position: 'bottom'
          }
        }
      },
      dataLabels: {
        enabled: true,
        textAnchor: 'start',
        offsetY: 8,
        offsetX: 11,
        style: {
          fontWeight: 500,
          fontSize: '0.9375rem',
          fontFamily: 'Inter'
        }
      },
      tooltip: {
        enabled: false
      },
      legend: {
        show: false
      },
      colors: [
        config.colors.primary,
        config.colors.success,
        config.colors.warning,
        config.colors.info,
        config.colors.danger
      ],
      grid: {
        strokeDashArray: 8,
        borderColor,
        xaxis: { lines: { show: true } },
        yaxis: { lines: { show: false } },
        padding: {
          top: -18,
          left: 21,
          right: 33,
          bottom: 10
        }
      },
      xaxis: {
        categories: ['US', 'IN', 'JA', 'CA', 'AU'],
        labels: {
          formatter: function (val) {
            return Number(val / 1000) + 'K';
          },
          style: {
            fontSize: '13px',
            colors: labelColor,
            fontFamily: 'Inter'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            fontWeight: 500,
            fontSize: '0.9375rem',
            colors: headingColor,
            fontFamily: 'Inter'
          }
        }
      },
      states: {
        hover: {
          filter: {
            type: 'none'
          }
        },
        active: {
          filter: {
            type: 'none'
          }
        }
      }
    };
  if (typeof salesCountryChartEl !== undefined && salesCountryChartEl !== null) {
    const salesCountryChart = new ApexCharts(salesCountryChartEl, salesCountryChartConfig);
    salesCountryChart.render();
  }

  const weeklySalesEl = document.querySelector('#weeklySalesChart'),
    weeklySalesConfig = {
      chart: {
        stacked: true,
        type: 'line',
        height: 235,
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      tooltip: { enabled: false },
      series: [
        {
          type: 'column',
          name: 'Earning',
          data: [90, 52, 67, 45, 75, 55, 48]
        },
        {
          type: 'column',
          name: 'Expense',
          data: [-53, -29, -67, -84, -60, -40, -77]
        },
        {
          type: 'line',
          name: 'Expense',
          data: [73, 20, 50, -20, 58, 15, 31]
        }
      ],
      plotOptions: {
        bar: {
          borderRadius: 8,
          columnWidth: '57%',
          borderRadiusApplication: 'end'
        }
      },
      markers: {
        size: 4,
        strokeWidth: 3,
        fillOpacity: 1,
        strokeOpacity: 1,
        colors: [cardColor],
        strokeColors: config.colors.warning
      },
      stroke: {
        curve: 'smooth',
        width: [0, 0, 3],
        colors: [config.colors.warning]
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        show: false
      },
      colors: [config.colors.primary, config.colors_label.primary],
      grid: {
        yaxis: { lines: { show: false } },
        padding: {
          top: -28,
          left: -6,
          right: -8,
          bottom: -5
        }
      },
      xaxis: {
        axisTicks: { show: false },
        axisBorder: { show: false },
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        labels: {
          style: {
            colors: labelColor,
            fontFamily: 'Inter',
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        max: 100,
        min: -100,
        show: false
      },
      states: {
        hover: {
          filter: {
            type: 'none'
          }
        },
        active: {
          filter: {
            type: 'none'
          }
        }
      }
    };
  if (typeof weeklySalesEl !== undefined && weeklySalesEl !== null) {
    const weeklySales = new ApexCharts(weeklySalesEl, weeklySalesConfig);
    weeklySales.render();
  }

  // Visits By Day Bar Chart
  // --------------------------------------------------------------------
  const visitsByDayChartEl = document.querySelector('#visitsByDayChart'),
    visitsByDayChartConfig = {
      chart: {
        height: 240,
        type: 'bar',
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          borderRadius: 8,
          distributed: true,
          columnWidth: '55%',
          endingShape: 'rounded',
          startingShape: 'rounded'
        }
      },
      series: [
        {
          data: [38, 55, 48, 65, 80, 38, 48]
        }
      ],
      tooltip: {
        enabled: false
      },
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      colors: [
        config.colors_label.warning,
        config.colors.warning,
        config.colors_label.warning,
        config.colors.warning,
        config.colors.warning,
        config.colors_label.warning,
        config.colors_label.warning
      ],
      grid: {
        show: false,
        padding: {
          top: -15,
          left: -7,
          right: -4
        }
      },
      states: {
        hover: {
          filter: {
            type: 'none'
          }
        },
        active: {
          filter: {
            type: 'none'
          }
        }
      },
      xaxis: {
        axisTicks: {
          show: false
        },
        axisBorder: {
          show: false
        },
        categories: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px',
            fontFamily: 'Inter'
          }
        }
      },
      yaxis: { show: false },
      responsive: [
        {
          breakpoint: 1025,
          options: {
            chart: {
              height: 210
            }
          }
        }
      ]
    };
  if (typeof visitsByDayChartEl !== undefined && visitsByDayChartEl !== null) {
    const visitsByDayChart = new ApexCharts(visitsByDayChartEl, visitsByDayChartConfig);
    visitsByDayChart.render();
  }
})();
