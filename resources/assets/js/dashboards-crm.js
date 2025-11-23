/**
 * Analytics Cards
 */

'use strict';
(function () {
  let cardColor,
    headingColor,
    labelColor,
    fontFamily,
    borderColor,
    bodyColor,
    legendColor,
    grayColor,
    heatMap1,
    heatMap2,
    heatMap3,
    heatMap4,
    currentTheme,
    chartBgColor;

  if (isDarkStyle) {
    heatMap1 = '#333457';
    heatMap2 = '#3c3e75';
    heatMap3 = '#484b9b';
    heatMap4 = '#696cff';
    chartBgColor = '#474360';
    grayColor = '#3b3e59';
    currentTheme = 'dark';
  } else {
    heatMap1 = '#ededff';
    heatMap2 = '#d5d6ff';
    heatMap3 = '#b7b9ff';
    heatMap4 = '#696cff';
    grayColor = '#f4f4f6';
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
      series1: config.colors.warning,
      series2: '#fdb528cc',
      series3: '#fdb52899',
      series4: '#fdb52866',
      series5: config.colors_label.warning
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

  // Total Profit Chart
  // --------------------------------------------------------------------
  const totalProfitChartEl = document.querySelector('#totalProfitChart'),
    totalProfitChartConfig = {
      chart: {
        type: 'bar',
        height: 100,
        parentHeightOffset: 0,
        stacked: true,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: 'PRODUCT A',
          data: [44, 21, 56, 34, 19]
        },
        {
          name: 'PRODUCT B',
          data: [-22, -25, -22, -17, -25]
        }
      ],
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '28%',
          borderRadius: 3.5,
          startingShape: 'rounded',
          endingShape: 'rounded',
          borderRadiusApplication: 'around'
        }
      },
      dataLabels: {
        enabled: false
      },
      tooltip: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 1,
        lineCap: 'round',
        colors: [cardColor]
      },
      legend: {
        show: false
      },
      colors: [config.colors.secondary, config.colors.danger],
      grid: {
        show: false,
        padding: {
          top: -61,
          right: -60,
          left: 0,
          bottom: -26
        }
      },
      xaxis: {
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
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
      },
      responsive: [
        {
          breakpoint: 1350,
          options: {
            chart: {
              height: 80
            },
            grid: {
              padding: {
                top: -10
              }
            },
            plotOptions: {
              bar: {
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 1200,
          options: {
            chart: {
              height: 100
            },
            plotOptions: {
              bar: {
                columnWidth: '20%'
              }
            }
          }
        },
        {
          breakpoint: 768,
          options: {
            chart: {
              height: 150
            },
            plotOptions: {
              bar: {
                columnWidth: '10%'
              }
            }
          }
        },
        {
          breakpoint: 480,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '20%'
              }
            }
          }
        }
      ]
    };
  if (typeof totalProfitChartEl !== undefined && totalProfitChartEl !== null) {
    const totalProfitChart = new ApexCharts(totalProfitChartEl, totalProfitChartConfig);
    totalProfitChart.render();
  }

  // Total Growth Chart
  // --------------------------------------------------------------------
  const totalGrowthChartEl = document.querySelector('#totalGrowthChart'),
    totalGrowthChartConfig = {
      chart: {
        height: 100,
        parentHeightOffset: 0,
        type: 'donut'
      },
      labels: [`${new Date().getFullYear()}`, `${new Date().getFullYear() - 1}`, `${new Date().getFullYear() - 2}`],
      series: [35, 30, 23],
      colors: [config.colors.primary, config.colors.success, config.colors.secondary],
      stroke: {
        width: 5,
        colors: cardColor
      },
      tooltip: {
        y: {
          formatter: function (val, opt) {
            return parseInt(val) + '%';
          }
        }
      },
      dataLabels: {
        enabled: false,
        formatter: function (val, opt) {
          return parseInt(val) + '%';
        }
      },
      states: {
        hover: {
          filter: { type: 'none' }
        },
        active: {
          filter: { type: 'none' }
        }
      },
      legend: {
        show: false
      },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              value: {
                fontSize: '1rem',
                fontFamily: 'Inter',
                color: bodyColor,
                fontWeight: 500,
                offsetY: 15,
                formatter: function (val) {
                  return parseInt(val) + '%';
                }
              },
              name: {
                show: false
              },
              total: {
                label: '',
                show: true,
                fontSize: '1.5rem',
                fontWeight: 500,
                formatter: function (w) {
                  return '12%';
                }
              }
            }
          }
        }
      }
    };
  if (typeof totalGrowthChartEl !== undefined && totalGrowthChartEl !== null) {
    const totalGrowthChart = new ApexCharts(totalGrowthChartEl, totalGrowthChartConfig);
    totalGrowthChart.render();
  }

  // Project Timeline Range Bar Chart
  // --------------------------------------------------------------------
  const projectTimelineEl = document.querySelector('#projectTimelineChart'),
    labels = ['Development Apps', 'UI Design', 'IOS Application', 'Web App Wireframing', 'Prototyping'],
    labelsResponsive = ['Development', 'UI Design', 'Application', 'App Wireframing', 'Prototyping'],
    projectTimelineConfig = {
      chart: {
        height: 240,
        type: 'rangeBar',
        parentHeightOffset: 0,
        toolbar: { show: false }
      },
      series: [
        {
          data: [
            {
              x: 'Catherine',
              y: [
                new Date(`${new Date().getFullYear()}-01-01`).getTime(),
                new Date(`${new Date().getFullYear()}-05-02`).getTime()
              ],
              fillColor: config.colors.primary
            },
            {
              x: 'Janelle',
              y: [
                new Date(`${new Date().getFullYear()}-02-18`).getTime(),
                new Date(`${new Date().getFullYear()}-05-30`).getTime()
              ],
              fillColor: config.colors.success
            },
            {
              x: 'Wellington',
              y: [
                new Date(`${new Date().getFullYear()}-02-07`).getTime(),
                new Date(`${new Date().getFullYear()}-05-31`).getTime()
              ],
              fillColor: config.colors.secondary
            },
            {
              x: 'Blake',
              y: [
                new Date(`${new Date().getFullYear()}-01-14`).getTime(),
                new Date(`${new Date().getFullYear()}-06-30`).getTime()
              ],
              fillColor: config.colors.info
            },
            {
              x: 'Quinn',
              y: [
                new Date(`${new Date().getFullYear()}-04-01`).getTime(),
                new Date(`${new Date().getFullYear()}-07-31`).getTime()
              ],
              fillColor: config.colors.warning
            }
          ]
        }
      ],

      tooltip: { enabled: false },
      plotOptions: {
        bar: {
          horizontal: true,
          borderRadius: 10,
          distributed: true,
          endingShape: 'rounded',
          startingShape: 'rounded',
          dataLabels: {
            hideOverflowingLabels: false
          }
        }
      },
      fill: {
        opacity: 1
      },
      stroke: {
        width: 2,
        colors: [cardColor]
      },
      dataLabels: {
        enabled: true,
        style: {
          fontWeight: 400,
          fontFamily: 'Inter',
          fontSize: '13px'
        },
        formatter: function (val, opts) {
          return labels[opts.dataPointIndex];
        }
      },
      states: {
        hover: { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
      },
      legend: { show: false },
      grid: {
        strokeDashArray: 6,
        borderColor,
        xaxis: { lines: { show: true } },
        yaxis: { lines: { show: false } },
        padding: {
          top: -32,
          left: 15,
          right: 18,
          bottom: 4
        }
      },
      xaxis: {
        type: 'datetime',
        axisTicks: { show: false },
        axisBorder: { show: false },
        labels: {
          style: {
            colors: labelColor,
            fontFamily: 'Inter',
            fontSize: '13px'
          },
          datetimeFormatter: {
            year: 'MMM',
            month: 'MMM'
          }
        }
      },
      yaxis: {
        labels: {
          show: true,
          align: 'left',
          style: {
            fontFamily: 'Inter',
            fontSize: '13px',
            colors: bodyColor
          }
        }
      },
      responsive: [
        {
          breakpoint: 446,
          options: {
            dataLabels: {
              formatter: function (val, opts) {
                return labelsResponsive[opts.dataPointIndex];
              }
            }
          }
        }
      ]
    };
  if (typeof projectTimelineEl !== undefined && projectTimelineEl !== null) {
    const projectTimeline = new ApexCharts(projectTimelineEl, projectTimelineConfig);
    projectTimeline.render();
  }

  // Organic Sessions Donut Chart
  // --------------------------------------------------------------------
  const organicSessionsEl = document.querySelector('#organicSessionsChart'),
    organicSessionsConfig = {
      chart: {
        height: 322,
        type: 'donut',
        parentHeightOffset: 0
      },
      labels: ['USA', 'India', 'Canada', 'Japan', 'France'],
      tooltip: { enabled: false },
      dataLabels: { enabled: false },
      stroke: {
        width: 3,
        lineCap: 'round',
        colors: [cardColor]
      },
      states: {
        hover: {
          filter: { type: 'none' }
        },
        active: {
          filter: { type: 'none' }
        }
      },
      plotOptions: {
        pie: {
          endAngle: 130,
          startAngle: -130,
          customScale: 0.9,
          donut: {
            size: '83%',
            labels: {
              show: true,
              name: {
                offsetY: 25,
                fontSize: '13px',
                fontFamily: 'Inter',
                color: bodyColor
              },
              value: {
                offsetY: -15,
                fontWeight: 500,
                fontSize: '1.75rem',
                fontFamily: 'Inter',
                color: headingColor,
                formatter: function (val) {
                  return parseInt(val) + 'K';
                }
              },
              total: {
                show: true,
                label: '2022',
                fontSize: '0.9375rem',
                fontFamily: 'Inter',
                color: bodyColor,
                formatter: function (w) {
                  return '89K';
                }
              }
            }
          }
        }
      },
      series: [13, 18, 18, 24, 16],
      tooltip: {
        enabled: false
      },
      legend: {
        position: 'bottom',
        fontFamily: 'Inter',
        fontSize: '15px',
        markers: { strokeWidth: 0 },
        itemMargin: {
          horizontal: 24,
          vertical: 8
        },
        labels: {
          colors: headingColor
        }
      },
      colors: [
        chartColors.donut.series1,
        chartColors.donut.series2,
        chartColors.donut.series3,
        chartColors.donut.series4,
        chartColors.donut.series5
      ]
    };
  if (typeof organicSessionsEl !== undefined && organicSessionsEl !== null) {
    const organicSessions = new ApexCharts(organicSessionsEl, organicSessionsConfig);
    organicSessions.render();
  }
  // Weekly Overview Line Chart
  // --------------------------------------------------------------------
  const weeklyOverviewChartEl = document.querySelector('#weeklyOverviewChart'),
    weeklyOverviewChartConfig = {
      chart: {
        type: 'line',
        height: 180,
        offsetY: -9,
        offsetX: -16,
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: 'Sales',
          type: 'column',
          data: [83, 68, 56, 65, 65, 50, 39]
        },
        {
          name: 'Sales',
          type: 'line',
          data: [63, 38, 31, 45, 46, 27, 18]
        }
      ],
      plotOptions: {
        bar: {
          borderRadius: 9,
          columnWidth: '35%',
          endingShape: 'rounded',
          startingShape: 'rounded',
          colors: {
            ranges: [
              {
                to: 50,
                from: 40,
                color: config.colors.primary
              }
            ]
          }
        }
      },
      markers: {
        size: 3.5,
        strokeWidth: 2,
        fillOpacity: 1,
        strokeOpacity: 1,
        colors: [cardColor],
        strokeColors: config.colors.primary
      },
      stroke: {
        width: [0, 2],
        colors: [config.colors.primary]
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        show: false
      },
      colors: [grayColor],
      grid: {
        strokeDashArray: 10,
        borderColor,
        padding: {
          bottom: -10
        }
      },
      xaxis: {
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        tickPlacement: 'on',
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        min: 0,
        max: 90,
        show: true,
        tickAmount: 3,
        labels: {
          formatter: function (val) {
            return parseInt(val) + 'K';
          },
          style: {
            fontSize: '13px',
            fontFamily: 'Inter',
            colors: labelColor
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
      responsive: [
        {
          breakpoint: 1462,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 1388,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '45%',
                borderRadius: 8
              }
            }
          }
        },
        {
          breakpoint: 1030,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '48%'
              }
            }
          }
        },
        {
          breakpoint: 992,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '28%'
              }
            }
          }
        },
        {
          breakpoint: 874,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '38%'
              }
            }
          }
        },
        {
          breakpoint: 768,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '28%',
                borderRadius: 10
              }
            }
          }
        },
        {
          breakpoint: 500,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 7
              }
            }
          }
        },
        {
          breakpoint: 393,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 6
              }
            }
          }
        }
      ]
    };
  if (typeof weeklyOverviewChartEl !== undefined && weeklyOverviewChartEl !== null) {
    const weeklyOverviewChart = new ApexCharts(weeklyOverviewChartEl, weeklyOverviewChartConfig);
    weeklyOverviewChart.render();
  }

  // Monthly Budget Area Chart
  // --------------------------------------------------------------------
  const monthlyBudgetChartEl = document.querySelector('#monthlyBudgetChart'),
    monthlyBudgetChartConfig = {
      series: [
        {
          data: [0, 85, 25, 125, 90, 250, 200, 350]
        }
      ],
      chart: {
        height: 235,
        parentHeightOffset: 0,
        parentWidthOffset: 0,
        toolbar: {
          show: false
        },
        type: 'area'
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        width: 5,
        curve: 'smooth'
      },
      legend: {
        show: false
      },
      markers: {
        size: 6,
        colors: 'transparent',
        strokeColors: 'transparent',
        strokeWidth: 4,
        discrete: [
          {
            fillColor: config.colors.white,
            seriesIndex: 0,
            dataPointIndex: 6,
            strokeColor: config.colors.success,
            strokeWidth: 2,
            size: 6,
            radius: 8
          }
        ],
        offsetX: -1,
        hover: {
          size: 7
        }
      },
      colors: [config.colors.success],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          gradientToColors: [config.colors.cardColor],
          opacityTo: 0.5,
          stops: [0, 100]
        }
      },
      grid: {
        show: false,
        padding: {
          left: 10,
          top: 0,
          right: 12
        }
      },
      xaxis: {
        type: 'numeric',
        labels: { show: false },
        axisTicks: { show: false },
        axisBorder: { show: false }
      },
      yaxis: { show: false },
      markers: {
        size: 1,
        offsetY: 1,
        offsetX: -5,
        strokeWidth: 4,
        strokeOpacity: 1,
        colors: ['transparent'],
        strokeColors: 'transparent',
        discrete: [
          {
            size: 7,
            seriesIndex: 0,
            dataPointIndex: 7,
            strokeColor: config.colors.success,
            fillColor: cardColor
          }
        ]
      }
    };
  if (typeof monthlyBudgetChartEl !== undefined && monthlyBudgetChartEl !== null) {
    const monthlyBudgetChart = new ApexCharts(monthlyBudgetChartEl, monthlyBudgetChartConfig);
    monthlyBudgetChart.render();
  }

  // External Links Stacked Bar Chart
  // --------------------------------------------------------------------
  const externalLinksChartEl = document.querySelector('#externalLinksChart'),
    externalLinksChartConfig = {
      chart: {
        type: 'bar',
        height: 330,
        parentHeightOffset: 0,
        stacked: true,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: 'Google Analytics',
          data: [155, 135, 320, 100, 150, 335, 160]
        },
        {
          name: 'Facebook Ads',
          data: [110, 235, 125, 230, 215, 115, 200]
        }
      ],
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '40%',
          borderRadius: 8,
          borderRadiusApplication: 'around',
          startingShape: 'rounded',
          endingShape: 'rounded'
        }
      },
      dataLabels: {
        enabled: false
      },
      tooltip: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 6,
        lineCap: 'round',
        colors: [cardColor]
      },
      legend: {
        show: false
      },
      colors: [config.colors.primary, config.colors.secondary],
      grid: {
        strokeDashArray: 10,
        borderColor,
        padding: {
          top: -12,
          left: -4,
          right: -5,
          bottom: 5
        }
      },
      xaxis: {
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
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
      },
      responsive: [
        {
          breakpoint: 1441,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '50%'
              }
            }
          }
        },
        {
          breakpoint: 1025,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '45%'
              }
            }
          }
        },
        {
          breakpoint: 577,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '35%'
              }
            }
          }
        },
        {
          breakpoint: 426,
          options: {
            plotOptions: {
              bar: {
                columnWidth: '50%'
              }
            }
          }
        }
      ]
    };
  if (typeof externalLinksChartEl !== undefined && externalLinksChartEl !== null) {
    const externalLinksChart = new ApexCharts(externalLinksChartEl, externalLinksChartConfig);
    externalLinksChart.render();
  }
})();
