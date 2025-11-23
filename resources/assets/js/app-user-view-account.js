/**
 * App User View - Account (js)
 */
'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Variable declaration for table
  const dt_project_table = document.querySelector('.datatable-project'),
    dt_invoice_table = document.querySelector('.datatable-invoice');

  // Project datatable
  // --------------------------------------------------------------------
  if (dt_project_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    tableTitle.innerHTML = 'Project List';
    var dt_project = new DataTable(dt_project_table, {
      ajax: assetsPath + 'json/projects-list.json', // JSON file to add data
      columns: [
        { data: 'hours' },
        { data: 'project_name' },
        { data: 'total_task' },
        { data: 'progress' },
        { data: 'hours' }
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
          // User full name and email
          targets: 1,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            var name = full['project_name'],
              framework = full['framework'],
              image = full['project_image'];

            var output = '';

            if (image) {
              // For Avatar image
              output =
                '<img src="' +
                assetsPath +
                'img/icons/brands/' +
                image +
                '" alt="Project Image" class="rounded-circle">';
            } else {
              // For Avatar badge
              var stateNum = Math.floor(Math.random() * 6) + 1;
              var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              var state = states[stateNum],
                name = full['full_name'],
                initials = name.match(/\b\w/g) || [];
              initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
              output = '<span class="avatar-initial rounded-circle bg-label-' + state + '">' + initials + '</span>';
            }

            // Creates full output for row
            var rowOutput =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-3">' +
              output +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<span class="text-truncate fw-medium text-heading">' +
              name +
              '</span>' +
              '<small>' +
              framework +
              '</small>' +
              '</div>' +
              '</div>';

            return rowOutput;
          }
        },
        {
          // Task
          targets: 2,
          render: function (data, type, full, meta) {
            var task = full['total_task'];
            return `<span class="text-heading">${task}</span>`;
          }
        },
        {
          // Label
          targets: 3,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            var progress = full['progress'] + '%',
              color,
              labelColor;

            switch (true) {
              case full['progress'] < 25:
                color = 'bg-danger';
                labelColor = 'bg-label-danger';
                break;
              case full['progress'] < 50:
                color = 'bg-warning';
                labelColor = 'bg-label-warning';
                break;
              case full['progress'] < 75:
                color = 'bg-info';
                labelColor = 'bg-label-info';
                break;
              case full['progress'] <= 100:
                color = 'bg-success';
                labelColor = 'bg-label-success';
                break;
            }

            return (
              '<div class="d-flex flex-column"><p class="mb-0 text-heading">' +
              progress +
              '</p>' +
              '<div class="progress rounded ' +
              labelColor +
              ' w-100 me-3" style="height: 6px;">' +
              '<div class="progress-bar rounded ' +
              color +
              '" style="width: ' +
              progress +
              '" aria-valuenow="' +
              progress +
              '" aria-valuemin="0" aria-valuemax="100"></div>' +
              '</div>' +
              '</div>'
            );
          }
        },
        { targets: 4, orderable: false }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: { rowClass: 'row mx-md-2 my-0 justify-content-between', features: [tableTitle] },
        topEnd: { search: { placeholder: 'Search Project', text: '_INPUT_' } },
        bottomStart: {},
        bottomEnd: {}
      },
      displayLength: 7,
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
              return 'Details of ' + data['project_name'];
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
    //? The 'delete-record' class is necessary for the functionality of the following code.
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('delete-record')) {
        dt_project.row(e.target.closest('tr')).remove().draw();
        const modalEl = document.querySelector('.dtr-bs-modal');
        if (modalEl && modalEl.classList.contains('show')) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal?.hide();
        }
      }
    });
  }

  // Invoice datatable
  // --------------------------------------------------------------------
  if (dt_invoice_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    tableTitle.innerHTML = 'Invoice List';
    const dt_invoice = new DataTable(dt_invoice_table, {
      ajax: assetsPath + 'json/invoice-list.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'invoice_id' },
        { data: 'invoice_status' },
        { data: 'total' },
        { data: 'issued_date' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // Invoice ID
          targets: 1,
          render: (data, type, full, meta) => {
            const invoiceId = full['invoice_id'];
            // Creates full output for row
            const rowOutput = `<a href="${baseUrl}app/invoice/preview"><span>#${invoiceId}</span></a>`;
            return rowOutput;
          }
        },
        {
          // Invoice status
          targets: 2,
          render: (data, type, full, meta) => {
            const invoiceStatus = full['invoice_status'];
            const dueDate = full['due_date'];
            const balance = full['balance'];

            const roleBadgeObj = {
              Sent: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-secondary w-px-30 h-px-30"><i class="icon-base ri ri-mail-line icon-16px"></i></span>`,
              Draft: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-primary w-px-30 h-px-30"><i class="icon-base ri ri-folder-line icon-16px"></i></span>`,
              'Past Due': `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-danger w-px-30 h-px-30"><i class="icon-base ri ri-alert-line icon-16px"></i></span>`,
              'Partial Payment': `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-success w-px-30 h-px-30"><i class="icon-base ri ri-check-line icon-16px"></i></span>`,
              Paid: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-warning w-px-30 h-px-30"><i class="icon-base ri ri-pie-chart-line icon-16px"></i></span>`,
              Downloaded: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-info w-px-30 h-px-30"><i class="icon-base ri ri-arrow-down-line icon-16px"></i></span>`
            };

            return `
              <span class='d-inline-block' data-bs-toggle='tooltip' data-bs-html='true'
                    title='<span>${invoiceStatus}<br>
                           <span class="fw-medium">Balance:</span> ${balance}<br>
                           <span class="fw-medium">Due Date:</span> ${dueDate}</span>'>
                ${roleBadgeObj[invoiceStatus]}
              </span>
            `;
          }
        },
        {
          // Total Invoice Amount
          targets: 3,
          render: function (data, type, full, meta) {
            const total = full['total'];
            return '$' + total;
          }
        },
        {
          // Due Date
          targets: 4,
          render: function (data, type, full, meta) {
            var issuedDate = new Date(full['issued_date']);

            // Formatting date manually
            const formattedDate = new Intl.DateTimeFormat('en-GB', {
              day: '2-digit',
              month: 'short',
              year: 'numeric'
            }).format(issuedDate);

            // Generating a hidden span with a formatted date for sorting
            const hiddenDate = issuedDate.toISOString().split('T')[0].replace(/-/g, ''); // Converts to 'YYYYMMDD' format

            // Creates full output for row with plain JavaScript
            var rowOutput = `<span class="d-none">${hiddenDate}</span>${formattedDate}`;

            return rowOutput;
          }
        },
        {
          // Due Date
          targets: 4,
          render: function (data, type, full, meta) {
            var $due_date = new Date(full['issued_date']);
            // Creates full output for row
            var $row_output =
              '<span class="d-none">' +
              moment($due_date).format('YYYYMMDD') +
              '</span>' +
              moment($due_date).format('DD MMM YYYY');
            $due_date;
            return $row_output;
          }
        },
        {
          // Action
          targets: -1,
          title: 'Action',
          orderable: false,
          render: (data, type, full, meta) => {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon delete-record"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></a>
                <a href="${baseUrl}app/invoice/preview" class="btn btn-icon" data-bs-toggle="tooltip" title="Preview">
                  <i class="icon-base ri ri-eye-line icon-22px"></i>
                </a>
                <div class="d-inline-block">
                  <a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="icon-base ri ri-more-2-line icon-22px"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end m-0">
                    <a href="javascript:;" class="dropdown-item">Download</a>
                    <a href="${baseUrl}app/invoice/edit" class="dropdown-item">Edit</a>
                    <a href="javascript:;" class="dropdown-item">Duplicate</a>
                  </div>
                </div>
              </div>
            `;
          }
        }
      ],
      order: [[1, 'desc']],
      displayLength: 7,
      layout: {
        topStart: { rowClass: 'row mx-0 px-3', features: [tableTitle] },
        topEnd: {
          features: [
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-primary dropdown-toggle float-sm-end my-5',
                  text: '<i class="icon-base ri ri-upload-2-line icon-16px me-2"></i>Export',
                  buttons: [
                    {
                      extend: 'print',
                      text: '<i class="icon-base ri ri-printer-line me-2"></i>Print',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'csv',
                      text: '<i class="icon-base ri ri-file-text-line me-2"></i>Csv',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'excel',
                      text: '<i class="icon-base ri ri-file-excel-line me-2"></i>Excel',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'pdf',
                      text: '<i class="icon-base ri ri-file-pdf-line me-2"></i>Pdf',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'copy',
                      text: '<i class="icon-base ri ri-file-copy-line me-2"></i>Copy',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    }
                  ]
                }
              ]
            }
          ]
        },
        bottomStart: { rowClass: 'row mx-3 justify-content-between', features: ['info'] },
        bottomEnd: 'paging'
      },
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
              return 'Details of ' + data['invoice_id'];
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

    //? The 'delete-record' class is necessary for the functionality of the following code.
    function deleteRecord(event) {
      let row = document.querySelector('.dtr-expanded');
      if (event) {
        row = event.target.parentElement.closest('tr');
      }
      if (row) {
        dt_invoice.row(row).remove().draw();
      }
    }

    function bindDeleteEvent() {
      const dt_invoice_table = document.querySelector('.datatable-invoice');
      const modal = document.querySelector('.dtr-bs-modal');

      if (dt_invoice_table && dt_invoice_table.classList.contains('collapsed')) {
        if (modal) {
          modal.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord();
              const closeButton = modal.querySelector('.btn-close');
              if (closeButton) closeButton.click(); // Simulates a click on the close button
            }
          });
        }
      } else {
        const tableBody = dt_invoice_table?.querySelector('tbody');
        if (tableBody) {
          tableBody.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord(event);
            }
          });
        }
      }
    }

    // Initial event binding
    bindDeleteEvent();

    // Re-bind events when modal is shown or hidden
    document.addEventListener('show.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });

    document.addEventListener('hide.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });

    // On each datatable draw, initialize tooltip
    dt_invoice.on('draw.dt', function () {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, { boundary: document.body });
      });
    });
  }

  // Filter form control to default size
  // ? setTimeout used for project-list and invoice-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
      { selector: '.dt-length', classToAdd: 'mb-md-4 mb-0' },
      { selector: '.dt-buttons', classToAdd: 'justify-content-center' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' },
      { selector: '.dt-buttons .btn-group .btn', classToRemove: 'btn-secondary' }
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
