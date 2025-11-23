/**
 * Page eCommerce Referral
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_user_table = document.querySelector('.datatables-referral'),
    customerView = baseUrl + 'app/ecommerce/customer/details/overview',
    statusObj = {
      1: { title: 'Paid', class: 'bg-label-success' },
      2: { title: 'Unpaid', class: 'bg-label-warning' },
      3: { title: 'Rejected', class: 'bg-label-danger' }
    };

  // Users datatable
  if (dt_user_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    tableTitle.innerHTML = 'Referred users';
    var dt_user = new DataTable(dt_user_table, {
      ajax: assetsPath + 'json/ecommerce-referral.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'user' },
        { data: 'referred_id' },
        { data: 'status' },
        { data: 'value' },
        { data: 'earning' }
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
          checkboxes: { selectAllRender: '<input type="checkbox" class="form-check-input">' }
        },
        {
          // eCommerce full name and email
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            const userName = full['user'];
            const email = full['email'];
            const avatar = full['avatar'];
            let output;

            if (avatar) {
              // For Avatar image
              output = `<img src="${assetsPath}img/avatars/${avatar}" alt="Avatar" class="rounded-circle">`;
            } else {
              // For Avatar badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (userName.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();
              output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for row
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center customer-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-4">${output}</div>
                </div>
                <div class="d-flex flex-column">
                  <a href="${customerView}" class="text-heading"><span class="fw-medium">${userName}</span></a>
                  <small class="text-nowrap">${email}</small>
                </div>
              </div>`;

            return rowOutput;
          }
        },
        {
          // eCommerce Role
          targets: 3,
          render: function (data, type, full, meta) {
            let role = full['referred_id'];

            return '<span>' + role + '</span>';
          }
        },

        {
          // eCommerce Status
          targets: 4,
          render: function (data, type, full, meta) {
            let status = full['status'];

            return (
              '<span class="badge rounded-pill ' +
              statusObj[status].class +
              '" text-capitalized>' +
              statusObj[status].title +
              '</span>'
            );
          }
        },
        {
          // value
          targets: 5,
          render: function (data, type, full, meta) {
            let plan = full['value'];

            return '<span>' + plan + '</span>';
          }
        },
        {
          // earning
          targets: 6,
          render: function (data, type, full, meta) {
            let earn = full['earning'];

            return '<span class="text-heading">' + earn + '</span > ';
          }
        }
      ],
      select: { style: 'multi', selector: 'td:nth-child(2)' },
      order: [[2, 'asc']],
      layout: {
        topStart: { rowClass: 'row m-3 my-0 justify-content-between', features: [tableTitle] },
        topEnd: {
          features: [
            { pageLength: { menu: [10, 25, 50, 100], text: '_MENU_' } },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-primary dropdown-toggle me-4',
                  text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ri ri-upload-2-line icon-16px me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ri ri-printer-line me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Check if inner is HTML content
                            if (inner.indexOf('<') > -1) {
                              const parser = new DOMParser();
                              const doc = parser.parseFromString(inner, 'text/html');

                              // Get all text content
                              let text = '';

                              // Handle specific elements
                              const userNameElements = doc.querySelectorAll('.customer-name');
                              if (userNameElements.length > 0) {
                                userNameElements.forEach(el => {
                                  // Get text from nested structure
                                  const nameText =
                                    el.querySelector('.fw-medium')?.textContent ||
                                    el.querySelector('.d-block')?.textContent ||
                                    el.textContent;
                                  text += nameText.trim() + ' ';
                                });
                              } else {
                                // Get regular text content
                                text = doc.body.textContent || doc.body.innerText;
                              }

                              return text.trim();
                            }

                            return inner;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = config.colors.headingColor;
                        win.document.body.style.borderColor = config.colors.borderColor;
                        win.document.body.style.backgroundColor = config.colors.bodyBg;
                        const table = win.document.body.querySelector('table');
                        table.classList.add('compact');
                        table.style.color = 'inherit';
                        table.style.borderColor = 'inherit';
                        table.style.backgroundColor = 'inherit';
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ri ri-file-text-line me-1"></i>Csv</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ri ri-file-excel-line me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ri ri-file-pdf-line me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ri ri-file-copy-line me-1"></i>Copy`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
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
              return 'Details of ' + data['user'];
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
  }

  // Filter form control to default size
  // ? setTimeout used for referral table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-length', classToAdd: 'me-2 ms-n2 ms-sm-0' },
      { selector: '.dt-buttons', classToAdd: 'mb-md-0 mb-4 justify-content-center' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'mt-md-0 mt-4' },
      { selector: '.dt-layout-end', classToAdd: 'mt-0' },
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
