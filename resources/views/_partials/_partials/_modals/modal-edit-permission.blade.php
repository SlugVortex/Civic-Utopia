<!-- Edit Permission Modal -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-simple modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">Edit Permission</h4>
          <p>Edit permission as per your requirements.</p>
        </div>
        <div class="alert alert-warning d-flex align-items-start" role="alert">
          <span class="alert-icon me-4 rounded-1 p-1"><i class="icon-base ri ri-alert-line icon-22px"></i></span>
          <div>
            <h5 class="alert-heading mb-1">Warning</h5>
            <p class="mb-0">By editing the permission name, you might break the system permissions functionality.
              Please ensure you're absolutely certain before proceeding.</p>
          </div>
        </div>
        <form id="editPermissionForm" class="row pt-2 gx-4" onsubmit="return false">
          <div class="col-sm-9 form-control-validation mb-4">
            <input type="text" id="editPermissionName" name="editPermissionName" class="form-control form-control-sm"
              placeholder="Permission Name" tabindex="-1" />
          </div>
          <div class="col-sm-3 mb-4">
            <button type="submit" class="btn btn-primary mt-1 mt-sm-0">Update</button>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="editCorePermission" />
              <label class="form-check-label" for="editCorePermission"> Set as core permission </label>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit Permission Modal -->
