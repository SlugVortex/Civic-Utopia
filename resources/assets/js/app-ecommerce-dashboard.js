/**
 * eCommerce Dashboards
 */

'use strict';

(function () {
  let cardColor, labelColor, headingColor, bodyColor, chartBgColor, currentTheme;
  if (isDarkStyle) {
    chartBgColor = '#474360';
    currentTheme = 'dark';
  } else {
    chartBgColor = '#F0F2F8';
    currentTheme = 'light';
  }
  cardColor = config.colors.cardColor;
  labelColor = config.colors.textMuted;
  headingColor = config.colors.headingColor;
  bodyColor = config.colors.bodyColor;

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

  // Weekly sales with bg swiper loop and autoplay
  // --------------------------------------------------------------------
  const swiperWithBgPagination = document.querySelector('#swiper-weekly-sales-with-bg');
  if (swiperWithBgPagination) {
    new Swiper(swiperWithBgPagination, {
      loop: true,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false
      },
      pagination: {
        clickable: true,
        el: '.swiper-pagination'
      }
    });
  }

  // Sales This Month
  // --------------------------------------------------------------------
  const saleThisMonthChartEl = document.querySelector('#saleThisMonth'),
    saleThisMonthChartConfig = {
      chart: {
        height: 100,
        type: 'line',
        parentHeightOffset: 0,
        toolbar: { show: false },
        dropShadow: {
          top: 14,
          blur: 4,
          left: 0,
          enabled: true,
          opacity: 0.12,
          color: config.colors.primary
        }
      },

      tooltip: { enabled: false },
      grid: {
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: false } },
        padding: {
          top: -12,
          left: -2,
          right: 8,
          bottom: -10
        }
      },
      colors: [config.colors.primary],
      stroke: {
        width: 5,
        lineCap: 'round'
      },
      series: [
        {
          data: [200, 200, 500, 500, 300, 300, 100, 100, 450, 450, 650, 650]
        }
      ],
      xaxis: {
        labels: { show: false },
        axisTicks: { show: false },
        axisBorder: { show: false }
      },
      yaxis: {
        min: 0,
        labels: { show: false }
      },
      responsive: [
        {
          breakpoint: 1441,
          options: {
            chart: {
              height: 125
            }
          }
        },
        {
          breakpoint: 1025,
          options: {
            chart: {
              height: 100
            }
          }
        }
      ]
    };
  if (typeof saleThisMonthChartEl !== undefined && saleThisMonthChartEl !== null) {
    const saleThisMonthChart = new ApexCharts(saleThisMonthChartEl, saleThisMonthChartConfig);
    saleThisMonthChart.render();
  }

  // Total Impression & Order Chart
  // --------------------------------------------------------------------

  // Impression & Order Chart Function
  function orderImpressionRadialBar(color, value, icon) {
    const orderImpressionRadialBarOpt = {
      chart: {
        height: 90,
        width: 90,
        type: 'radialBar',
        sparkline: {
          enabled: true
        }
      },
      plotOptions: {
        radialBar: {
          hollow: {
            size: '52%',
            image: icon,
            imageWidth: 24,
            imageHeight: 24,
            imageClipped: false
          },
          dataLabels: {
            name: {
              show: false
            },
            value: {
              show: false
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
      colors: [color],
      grid: {
        padding: {
          bottom: 0
        }
      },
      series: [value],
      labels: ['Progress'],
      responsive: [
        {
          breakpoint: 1441,
          options: {
            chart: {
              height: 103
            }
          }
        },
        {
          breakpoint: 1400,
          options: {
            chart: {
              height: 100
            }
          }
        },
        {
          breakpoint: 1380,
          options: {
            chart: {
              height: 95
            }
          }
        },
        {
          breakpoint: 1332,
          options: {
            chart: {
              height: 85
            }
          }
        },
        {
          breakpoint: 1265,
          options: {
            chart: {
              height: 75
            }
          }
        },
        {
          breakpoint: 1025,
          options: {
            chart: {
              height: 90
            }
          }
        }
      ]
    };
    return orderImpressionRadialBarOpt;
  }

  const chartProgressList = document.querySelectorAll('.chart-progress');
  if (chartProgressList) {
    chartProgressList.forEach(function (chartProgressEl) {
      const color = config.colors[chartProgressEl.dataset.color],
        series = chartProgressEl.dataset.series,
        icon = chartProgressEl.dataset.icon;
      const optionsBundle = orderImpressionRadialBar(color, series, icon);
      const chart = new ApexCharts(chartProgressEl, optionsBundle);
      chart.render();
    });
  }

  // Marketing and sales swiper loop and autoplay
  // --------------------------------------------------------------------
  const swiperMarketingPagination = document.querySelector('#swiper-marketing-sales');
  if (swiperMarketingPagination) {
    new Swiper(swiperMarketingPagination, {
      loop: true,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false
      },
      pagination: {
        clickable: true,
        el: '.swiper-pagination'
      }
    });
  }

  // Live Visitors Bar Chart
  // --------------------------------------------------------------------
  const liveVisitorsEl = document.querySelector('#liveVisitors'),
    liveVisitorsConfig = {
      chart: {
        height: 150,
        parentHeightOffset: 0,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: '43%',
          endingShape: 'rounded',
          startingShape: 'rounded'
        }
      },
      colors: [config.colors.success],
      grid: {
        padding: {
          top: -4,
          left: -8,
          right: -2,
          bottom: -7
        },
        yaxis: { lines: { show: false } }
      },
      dataLabels: {
        enabled: false
      },
      series: [
        {
          data: [70, 80, 92, 49, 19, 49, 23, 82, 65, 23, 49, 65, 65]
        }
      ],
      legend: {
        show: false
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
      responsive: [
        {
          breakpoint: 1441,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10
              }
            }
          }
        },
        {
          breakpoint: 1288,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 8
              }
            }
          }
        },
        {
          breakpoint: 1200,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10
              }
            }
          }
        },
        {
          breakpoint: 1025,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 8
              }
            }
          }
        },
        {
          breakpoint: 992,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 14
              }
            }
          }
        },
        {
          breakpoint: 645,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10
              }
            }
          }
        },
        {
          breakpoint: 474,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 7
              }
            }
          }
        },
        {
          breakpoint: 383,
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
  if (typeof liveVisitorsEl !== undefined && liveVisitorsEl !== null) {
    const liveVisitors = new ApexCharts(liveVisitorsEl, liveVisitorsConfig);
    liveVisitors.render();
  }

  // Visits By Day Bar Chart
  // --------------------------------------------------------------------
  const visitsByDayChartEl = document.querySelector('#visitsByDayChart'),
    visitsByDayChartConfig = {
      chart: {
        height: 314,
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
        config.colors_label.primary,
        config.colors.primary,
        config.colors_label.primary,
        config.colors.primary,
        config.colors.primary,
        config.colors_label.primary,
        config.colors_label.primary
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
