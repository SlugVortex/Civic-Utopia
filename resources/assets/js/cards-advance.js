/**
 * Advanced Cards
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let cardColor, headingColor, legendColor, labelColor, fontFamily, currentTheme;
  cardColor = config.colors.cardColor;
  labelColor = config.colors.textMuted;
  legendColor = config.colors.bodyColor;
  headingColor = config.colors.headingColor;
  fontFamily = config.fontFamily;

  if (isDarkStyle) {
    currentTheme = 'dark';
  } else {
    currentTheme = 'light';
  }

  // Radial bar chart functions
  function radialBarChart(color, value, show) {
    const radialBarChartOpt = {
      chart: {
        height: show == 'true' ? 58 : 55,
        width: show == 'true' ? 58 : 45,
        type: 'radialBar'
      },
      plotOptions: {
        radialBar: {
          hollow: {
            size: show == 'true' ? '50%' : '30%'
          },
          dataLabels: {
            show: show == 'true' ? true : false,
            value: {
              offsetY: -10,
              fontSize: '15px',
              fontWeight: 500,
              fontFamily: fontFamily,
              color: headingColor
            }
          },
          track: {
            background: config.colors_label.secondary
          }
        }
      },
      stroke: {
        lineCap: 'round'
      },
      colors: [color],
      grid: {
        padding: {
          top: show == 'true' ? -12 : -15,
          bottom: show == 'true' ? -17 : -15,
          left: show == 'true' ? -17 : -5,
          right: -15
        }
      },
      series: [value],
      labels: show == 'true' ? [''] : ['Progress']
    };
    return radialBarChartOpt;
  }

  // Progress Chart
  // --------------------------------------------------------------------
  // All progress chart
  const chartProgressList = document.querySelectorAll('.chart-progress');
  if (chartProgressList) {
    chartProgressList.forEach(function (chartProgressEl) {
      const color = config.colors[chartProgressEl.dataset.color],
        series = chartProgressEl.dataset.series;
      const progress_variant = chartProgressEl.dataset.progress_variant
        ? chartProgressEl.dataset.progress_variant
        : 'false';
      const optionsBundle = radialBarChart(color, series, progress_variant);
      const chart = new ApexCharts(chartProgressEl, optionsBundle);
      chart.render();
    });
  }

  // Website Statistic
  const webVisitorsEl = document.querySelector('#webVisitors'),
    webVisitorsConfig = {
      chart: {
        height: 90,
        width: 160,
        parentHeightOffset: 0,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          barHeight: '85%',
          columnWidth: '35%',
          startingShape: 'rounded',
          endingShape: 'rounded',
          borderRadius: 3,
          distributed: true
        }
      },
      colors: [config.colors.primary],
      grid: {
        padding: {
          top: -40,
          left: -12
        },
        yaxis: { lines: { show: false } }
      },
      dataLabels: {
        enabled: false
      },
      series: [
        {
          data: [50, 40, 130, 100, 75, 100, 45, 35]
        }
      ],
      tooltip: {
        enabled: false
      },
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
      }
    };
  if (typeof webVisitorsEl !== undefined && webVisitorsEl !== null) {
    const webVisitors = new ApexCharts(webVisitorsEl, webVisitorsConfig);
    webVisitors.render();
  }

  // Credit Card Validation
  // --------------------------------------------------------------------

  const creditCardPayment = document.querySelector('.credit-card-payment'),
    expiryDatePayment = document.querySelector('.expiry-date-payment'),
    cvvMaskList = document.querySelectorAll('.cvv-code-payment');

  // Credit Card Cleave Masking
  if (creditCardPayment) {
    creditCardPayment.addEventListener('input', event => {
      creditCardPayment.value = formatCreditCard(event.target.value);
      const cleanValue = event.target.value.replace(/\D/g, '');
      let type = getCreditCardType(cleanValue);
      if (type && type !== 'unknown' && type !== 'general') {
        document.querySelector('.card-payment-type').innerHTML =
          '<img src="' + assetsPath + 'img/icons/payments/' + type + '-cc.png" class="cc-icon-image" height="28"/>';
      } else {
        document.querySelector('.card-payment-type').innerHTML = '';
      }
    });
    registerCursorTracker({
      input: creditCardPayment,
      delimiter: ' '
    });
  }

  // Expiry Date Mask
  if (expiryDatePayment) {
    expiryDatePayment.addEventListener('input', event => {
      expiryDatePayment.value = formatDate(event.target.value, {
        delimiter: '/',
        datePattern: ['m', 'y']
      });
    });
    registerCursorTracker({
      input: expiryDatePayment,
      delimiter: '/'
    });
  }

  // All CVV field
  if (cvvMaskList) {
    cvvMaskList.forEach(function (cvvMaskEl) {
      cvvMaskEl.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        cvvMaskEl.value = formatNumeral(cleanValue, {
          numeral: true,
          numeralPositiveOnly: true
        });
      });
    });
  }

  // Read Only Ratings
  // --------------------------------------------------------------------
  const iconStar = document.querySelector('.icon-star-ratings');
  if (iconStar) {
    let ratings = new Raty(iconStar, {
      starType: 'i',
      starOff: 'icon-base icon-xl ri ri-star-fill text-body-secondary icon-24px',
      starOn: 'icon-base icon-xl ri ri-star-fill text-warning icon-24px'
    });
    ratings.init();
  }
});
