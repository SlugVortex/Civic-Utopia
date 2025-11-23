/**
 * App eCommerce Category List
 */

'use strict';

// Comment editor

const commentEditor = document.querySelector('.comment-editor');

if (commentEditor) {
  new Quill(commentEditor, {
    modules: { toolbar: '.comment-toolbar' },
    placeholder: 'Write a Comment...',
    theme: 'snow'
  });
}

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  var dt_category_list_table = document.querySelector('.datatables-category-list');

  //select2 for dropdowns in offcanvas

  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      select2Focus($this);
      $this.select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') //for dynamic placeholder
      });
    });
  }

  // Customers List Datatable

  if (dt_category_list_table) {
    var dt_category = new DataTable(dt_category_list_table, {
      ajax: assetsPath + 'json/ecommerce-category-list.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'categories' },
        { data: 'total_products' },
        { data: 'total_earnings' },
        { data: 'id' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
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
          responsivePriority: 4,
          checkboxes: true,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: { selectAllRender: '<input type="checkbox" class="form-check-input">' }
        },
        {
          targets: 2,
          responsivePriority: 2,
          render: function (data, type, full, meta) {
            const name = full['categories'];
            const categoryDetail = full['category_detail'];
            const image = full['cat_image'];
            const id = full['id'];
            let output;
            if (image) {
              // For Product image
              output = `<img src="${assetsPath}img/ecommerce-images/${image}" alt="Product-${id}" class="rounded">`;
            } else {
              // For Product badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (categoryDetail.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();
              output = `<span class="avatar-initial rounded-2 bg-label-${state}">${initials}</span>`;
            }
            // Creates full output for Categories and Category Detail
            const rowOutput = `
              <div class="d-flex align-items-center category-name">
                <div class="avatar-wrapper me-3 rounded-2 bg-label-secondary">
                  <div class="avatar">${output}</div>
                </div>
                <div class="d-flex flex-column justify-content-center">
                  <span class="text-heading text-wrap fw-medium">${name}</span>
                  <span class="text-truncate mb-0 d-none d-sm-block"><small>${categoryDetail}</small></span>
                </div>
              </div>`;
            return rowOutput;
          }
        },
        {
          // Total products
          targets: 3,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            const total_products = full['total_products'];
            return '<div class="text-sm-end">' + total_products + '</div>';
          }
        },
        {
          // Total Earnings
          targets: 4,
          orderable: false,
          render: function (data, type, full, meta) {
            const total_earnings = full['total_earnings'];
            return "<div class='mb-0 text-sm-end'>" + total_earnings + '</div';
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return `
              <div class="d-flex align-items-sm-center justify-content-sm-center">
                <button class="btn btn-icon btn-text-secondary rounded-pill waves-effect"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>
                <button class="btn btn-icon btn-text-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ri ri-more-2-fill icon-22px"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="javascript:void(0);" class="dropdown-item">View</a>
                  <a href="javascript:void(0);" class="dropdown-item">Suspend</a>
                </div>
              </div>
            `;
          }
        }
      ],
      select: { style: 'multi', selector: 'td:nth-child(2)' },
      order: [2, 'desc'],
      layout: {
        topStart: {
          rowClass: 'row m-3 me-2 ms-0 my-0 justify-content-between',
          features: [{ search: { placeholder: 'Search Category', text: '_INPUT_' } }]
        },
        topEnd: {
          features: {
            pageLength: { menu: [7, 10, 25, 50, 100], text: '_MENU_' },
            buttons: [
              {
                extend: 'collection',
                className: 'btn btn-outline-secondary dropdown-toggle me-4 waves-effect',
                text: '<span class="d-flex align-items-center"><i class="icon-base ri ri-upload-2-line icon-16px me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                buttons: [
                  {
                    extend: 'print',
                    text: `<span class="d-flex align-items-center"><i class="icon-base ri ri-printer-line me-1"></i>Print</span>`,
                    className: 'dropdown-item',
                    exportOptions: {
                      columns: [3, 4, 5, 6, 7],
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
                            const userNameElements = doc.querySelectorAll('.category-name');
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
                      columns: [3, 4, 5, 6, 7],
                      format: {
                        body: function (inner, coldex, rowdex) {
                          if (inner.length <= 0) return inner;

                          // Parse HTML content
                          const parser = new DOMParser();
                          const doc = parser.parseFromString(inner, 'text/html');

                          let text = '';

                          // Handle category-name elements specifically
                          const userNameElements = doc.querySelectorAll('.category-name');
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
                      columns: [3, 4, 5, 6, 7],
                      format: {
                        body: function (inner, coldex, rowdex) {
                          if (inner.length <= 0) return inner;

                          // Parse HTML content
                          const parser = new DOMParser();
                          const doc = parser.parseFromString(inner, 'text/html');

                          let text = '';

                          // Handle category-name elements specifically
                          const userNameElements = doc.querySelectorAll('.category-name');
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
                      columns: [3, 4, 5, 6, 7],
                      format: {
                        body: function (inner, coldex, rowdex) {
                          if (inner.length <= 0) return inner;

                          // Parse HTML content
                          const parser = new DOMParser();
                          const doc = parser.parseFromString(inner, 'text/html');

                          let text = '';

                          // Handle category-name elements specifically
                          const userNameElements = doc.querySelectorAll('.category-name');
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
                      columns: [3, 4, 5, 6, 7],
                      format: {
                        body: function (inner, coldex, rowdex) {
                          if (inner.length <= 0) return inner;

                          // Parse HTML content
                          const parser = new DOMParser();
                          const doc = parser.parseFromString(inner, 'text/html');

                          let text = '';

                          // Handle category-name elements specifically
                          const userNameElements = doc.querySelectorAll('.category-name');
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
              },
              {
                text: `<i class="icon-base ri ri-add-line icon-18px me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Category</span>`,
                className: 'add-new btn btn-primary',
                attr: { 'data-bs-toggle': 'offcanvas', 'data-bs-target': '#offcanvasEcommerceCategoryList' }
              }
            ]
          }
        },
        bottomStart: { rowClass: 'row mx-3 justify-content-between', features: ['info'] },
        bottomEnd: 'paging'
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
              return 'Details of ' + data['categories'];
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
  // ? setTimeout used for category-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search', classToAdd: 'mb-0 mb-md-5' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'mt-0' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0 mt-0' },
      { selector: '.dt-layout-end .dt-buttons.btn-group', classToAdd: 'mb-md-0 mb-5' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

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

//For form validation
(function () {
  const eCommerceCategoryListForm = document.getElementById('eCommerceCategoryListForm');

  //Add New customer Form Validation
  const fv = FormValidation.formValidation(eCommerceCategoryListForm, {
    fields: {
      categoryTitle: { validators: { notEmpty: { message: 'Please enter category title' } } },
      slug: { validators: { notEmpty: { message: 'Please enter slug' } } }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: 'is-valid',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.form-control-validation';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });
})();
