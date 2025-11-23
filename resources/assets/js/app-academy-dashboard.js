/**
 * Academy Dashboard charts and datatable
 */

'use strict';

// Hour pie chart

document.addEventListener('DOMContentLoaded', function (e) {
  let cardColor,
    headingColor,
    labelColor,
    fontFamily,
    borderColor,
    legendColor,
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
  legendColor = config.colors.bodyColor;
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

  // Time Spendings Chart
  const leadsReportChartEl = document.querySelector('#leadsReportChart'),
    leadsReportChartConfig = {
      chart: {
        height: 140,
        width: 130,
        parentHeightOffset: 0,
        type: 'donut',
        opacity: 1
      },
      labels: ['36h', '56h', '16h', '32h', '56h', '16h'],
      series: [23, 35, 10, 20, 35, 23],
      colors: [
        chartColors.donut2.series1,
        chartColors.donut2.series2,
        chartColors.donut2.series3,
        chartColors.donut2.series4,
        chartColors.donut2.series5,
        chartColors.donut2.series6
      ],
      stroke: {
        width: 0
      },
      dataLabels: {
        enabled: false,
        formatter: function (val, opt) {
          return parseInt(val) + '%';
        }
      },
      legend: {
        show: false
      },
      tooltip: {
        theme: currentTheme
      },
      grid: {
        padding: {
          top: 0
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '75%',
            labels: {
              show: true,
              value: {
                fontSize: '1.125rem',
                fontFamily: fontFamily,
                color: headingColor,
                fontWeight: 500,
                offsetY: -15,
                formatter: function (val) {
                  return parseInt(val) + '%';
                }
              },
              name: {
                offsetY: 20,
                fontFamily: fontFamily
              },
              total: {
                show: true,
                fontSize: '.9375rem',
                label: 'Total',
                color: labelColor,
                formatter: function (w) {
                  return '231h';
                }
              }
            }
          }
        }
      }
    };
  if (typeof leadsReportChartEl !== undefined && leadsReportChartEl !== null) {
    const leadsReportChart = new ApexCharts(leadsReportChartEl, leadsReportChartConfig);
    leadsReportChart.render();
  }

  // datatbale bar chart

  const horizontalBarChartEl = document.querySelector('#horizontalBarChart'),
    horizontalBarChartConfig = {
      chart: {
        height: 300,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          barHeight: '60%',
          distributed: true,
          startingShape: 'rounded',
          borderRadiusApplication: 'end',
          borderRadius: 7
        }
      },
      grid: {
        strokeDashArray: 10,
        borderColor: borderColor,
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
          top: -35,
          bottom: -12
        }
      },
      colors: [
        config.colors.primary,
        config.colors.info,
        config.colors.success,
        config.colors.secondary,
        config.colors.danger,
        config.colors.warning
      ],
      fill: {
        opacity: [1, 1, 1, 1, 1, 1]
      },
      dataLabels: {
        enabled: true,
        style: {
          colors: [config.colors.white],
          fontWeight: 400,
          fontSize: '13px',
          fontFamily: fontFamily
        },
        formatter: function (val, opts) {
          return horizontalBarChartConfig.labels[opts.dataPointIndex];
        },
        offsetX: 0,
        dropShadow: {
          enabled: false
        }
      },
      labels: ['UI Design', 'UX Design', 'Music', 'Animation', 'React', 'SEO'],
      series: [
        {
          data: [35, 20, 14, 12, 10, 9]
        }
      ],

      xaxis: {
        categories: ['6', '5', '4', '3', '2', '1'],
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontFamily: fontFamily,
            fontSize: '13px'
          },
          formatter: function (val) {
            return `${val}%`;
          }
        }
      },
      yaxis: {
        max: 35,
        labels: {
          style: {
            colors: [labelColor],
            fontFamily: fontFamily,
            fontSize: '13px'
          }
        }
      },
      tooltip: {
        enabled: true,
        style: {
          fontSize: '12px'
        },
        onDatasetHover: {
          highlightDataSeries: false
        },
        custom: function ({ series, seriesIndex, dataPointIndex, w }) {
          return '<div class="px-3 py-2">' + '<span>' + series[seriesIndex][dataPointIndex] + '%</span>' + '</div>';
        }
      },
      legend: {
        show: false
      }
    };
  if (typeof horizontalBarChartEl !== undefined && horizontalBarChartEl !== null) {
    const horizontalBarChart = new ApexCharts(horizontalBarChartEl, horizontalBarChartConfig);
    horizontalBarChart.render();
  }

  //radial Barchart

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
            size: show == 'true' ? '45%' : '25%'
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

  const chartProgressList = document.querySelectorAll('.chart-progress');
  if (chartProgressList) {
    chartProgressList.forEach(function (chartProgressEl) {
      const color = config.colors[chartProgressEl.dataset.color],
        series = chartProgressEl.dataset.series;
      const progress_variant = chartProgressEl.dataset.progress_variant;
      const optionsBundle = radialBarChart(color, series, progress_variant);
      const chart = new ApexCharts(chartProgressEl, optionsBundle);
      chart.render();
    });
  }

  // datatable

  // Variable declaration for table
  const dt_academy_course = document.querySelector('.datatables-academy-course'),
    logoObj = {
      angular:
        '<span class="badge bg-label-danger rounded p-1_5"><i class="icon-base ri ri-angularjs-line icon-28px"></i></span>',
      figma:
        '<span class="badge bg-label-warning rounded p-1_5"><i class="icon-base ri ri-pencil-line icon-28px"></i></span>',
      react:
        '<span class="badge bg-label-info rounded p-1_5"><i class="icon-base ri ri-reactjs-line icon-28px"></i></span>',
      art: '<span class="badge bg-label-success rounded p-1_5"><i class="icon-base ri ri-palette-line icon-28px"></i></span>',
      fundamentals:
        '<span class="badge bg-label-primary rounded p-1_5"><i class="icon-base ri ri-star-smile-line icon-28px"></i></span>'
    };

  if (dt_academy_course) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-nowrap', 'text-md-start', 'text-center');
    tableTitle.innerHTML = 'Course you are taking';
    let dt_course = new DataTable(dt_academy_course, {
      ajax: assetsPath + 'json/app-academy-dashboard.json',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'course name' },
        { data: 'time' },
        { data: 'progress' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          searchable: false,
          responsivePriority: 3,
          checkboxes: true,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          targets: 2,
          responsivePriority: 2,
          render: (data, type, full) => {
            const { logo, course, user, image } = full;

            const output = image
              ? `<img src="${assetsPath}img/avatars/${image}" alt="Avatar" class="rounded-circle">`
              : (() => {
                  // Generate initials and random state for badge
                  const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                  const state = states[Math.floor(Math.random() * states.length)];
                  const initials = (user.match(/\b\w/g) || []).reduce((acc, char) => acc + char.toUpperCase(), '');
                  return `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
                })();

            // Create full row output
            return `
                  <div class="d-flex align-items-center">
                      <span class="me-4">${logoObj[logo]}</span>
                      <div>
                          <a class="text-heading text-truncate fw-medium mb-2 text-wrap" href="${baseUrl}app/academy/course-details">
                              ${course}
                          </a>
                          <div class="d-flex align-items-center mt-1">
                              <div class="avatar-wrapper me-2">
                                  <div class="avatar avatar-xs">
                                      ${output}
                                  </div>
                              </div>
                              <small class="text-nowrap text-heading">${user}</small>
                          </div>
                      </div>
                  </div>
              `;
          }
        },
        {
          targets: 3,
          responsivePriority: 3,
          render: data => {
            const duration = moment.duration(data);
            const hours = Math.floor(duration.asHours());
            const minutes = Math.floor(duration.asMinutes()) - hours * 60;
            const formattedTime = `${hours}h ${minutes}m`;

            return `<span class="fw-medium text-nowrap text-heading">${formattedTime}</span>`;
          }
        },
        {
          targets: 4,
          render: (data, type, full) => {
            const { status: statusNumber, number: averageNumber } = full;

            return `
                  <div class="d-flex align-items-center gap-3">
                      <p class="fw-medium mb-0 text-heading">${statusNumber}</p>
                      <div class="progress bg-label-primary w-100" style="height: 8px;">
                          <div
                              class="progress-bar"
                              style="width: ${statusNumber}"
                              aria-valuenow="${statusNumber}"
                              aria-valuemin="0"
                              aria-valuemax="100">
                          </div>
                      </div>
                      <small>${averageNumber}</small>
                  </div>
              `;
          }
        },
        {
          targets: 5,
          render: (data, type, full) => {
            const { user_number: userNumber, note, view } = full;

            return `
                  <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center w-px-75">
                          <i class="icon-base ri ri-group-line icon-24px me-1_5 text-primary"></i>
                          <span>${userNumber}</span>
                      </div>
                      <div class="d-flex align-items-center w-px-75">
                          <i class="icon-base ri ri-computer-line icon-24px me-1_5 text-info"></i>
                          <span>${note}</span>
                      </div>
                      <div class="d-flex align-items-center w-px-75">
                          <i class="icon-base ri ri-video-upload-line icon-24px me-1_5 text-danger scaleX-n1-rtl"></i>
                          <span>${view}</span>
                      </div>
                  </div>
              `;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row card-header mx-0 px-5 py-md-0',
          features: [tableTitle]
        },
        topEnd: {
          search: {
            placeholder: 'Search Course',
            text: '_INPUT_'
          }
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      lengthMenu: [5],
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['order'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              table.classList.add('datatables-basic');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for data-table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'px-0' },
      { selector: '.dt-layout-end', classToAdd: 'px-0' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
});
