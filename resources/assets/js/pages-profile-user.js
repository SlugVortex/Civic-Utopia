/**
 * profile - user (jquery)
 */
'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Variable declaration for table
  const dt_project_table = document.querySelector('.datatable-project');
  let dt_project;

  // Project datatable
  // --------------------------------------------------------------------
  if (dt_project_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0');
    tableTitle.innerHTML = 'Project List';
    dt_project = new DataTable(dt_project_table, {
      ajax: assetsPath + 'json/pages-profile-user.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'project_name' },
        { data: 'leader' },
        { data: 'avatar' },
        { data: 'progress' },
        { data: 'id' }
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
          // User full name and email
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            const name = full['project_name'];
            const framework = full['framework'];
            const image = full['project_image'];

            let output = '';

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
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];

              const fullName = full['full_name'] || '';
              let initials = fullName.match(/\b\w/g) || [];

              initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();

              output = '<span class="avatar-initial rounded-circle bg-label-' + state + '">' + initials + '</span>';
            }

            // Creates full output for row
            const rowOutput =
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
          targets: 3,
          render: function (data, type, full, meta) {
            const task = full['leader'];
            return '<span class="text-heading">' + task + '</span>';
          }
        },
        {
          // Progress
          targets: 5,
          render: function (data, type, full, meta) {
            const progress = full['progress'];

            const progressOutput =
              '<div class="d-flex align-items-center">' +
              '<div class="progress rounded-pill w-px-75 bg-label-primary" style="height: 6px;">' +
              '<div class="progress-bar" role="progressbar" style="width:' +
              progress +
              '%;" aria-valuenow="' +
              progress +
              '" aria-valuemin="0" aria-valuemax="100"></div>' +
              '</div>' +
              '<div class="text-heading ms-3">' +
              progress +
              '%</div>' +
              '</div>';

            return progressOutput;
          }
        },
        {
          // Avatar
          targets: 4,
          render: function (data, type, full, meta) {
            const avatarArray = full['avatar'];
            let avatarItems = '';
            let avatarCount = 0;

            for (let i = 0; i < avatarArray.length; i++) {
              avatarItems +=
                '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Kim Karlos" class="avatar avatar-sm pull-up">' +
                '<img class="rounded-circle" src="' +
                assetsPath +
                'img/avatars/' +
                avatarArray[i] +
                '.png" alt="Avatar">' +
                '</li>';
              avatarCount++;
              if (avatarCount > 2) break;
            }

            if (avatarCount > 2) {
              const remainingAvatars = avatarArray.length - 3;
              if (remainingAvatars > 0) {
                avatarItems +=
                  '<li class="avatar avatar-sm">' +
                  '<span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-bs-placement="top" title="' +
                  remainingAvatars +
                  ' more">+' +
                  remainingAvatars +
                  '</span>' +
                  '</li>';
              }
            }

            const avatarOutput =
              '<div class="d-flex align-items-center">' +
              '<ul class="list-unstyled d-flex align-items-center avatar-group mb-0 z-2">' +
              avatarItems +
              '</ul>' +
              '</div>';

            return avatarOutput;
          }
        },
        {
          // Actions
          targets: -1, // last column
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div>' +
              '<div class="dropdown">' +
              '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary dropdown-toggle hide-arrow rounded-pill waves-effect" data-bs-toggle="dropdown">' +
              '<i class="icon-base ri ri-more-2-line icon-22px"></i>' +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item">Download</a>' +
              '<a href="javascript:;" class="dropdown-item">Delete</a>' +
              '<a href="javascript:;" class="dropdown-item">View</a>' +
              '</div>' +
              '</div>' +
              '</div>'
            );
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
          rowClass: 'row card-header mx-0 px-2',
          features: [tableTitle]
        },
        topEnd: {},
        bottomStart: {},
        bottomEnd: {}
      },
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['full_name'];
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
    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
      const elementsToModify = [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' }
      ];

      // Delete record
      elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(element => {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
          if (classToAdd) {
            element.classList.add(classToAdd);
          }
        });
      });
    }, 100);
  }
});
