<div class="modal fade" id="editAssetModal" tabindex="-1" aria-labelledby="editAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssetModalLabel">Edit Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="inventory.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_asset_id" name="asset_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_asset_name" class="form-label">Asset Name</label>
                                <input type="text" class="form-control" id="edit_asset_name" name="asset_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_asset_category" class="form-label">Category</label>
                                <select class="form-select" id="edit_asset_category" name="asset_category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_name']; ?>"><?php echo $category['category_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="edit_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label for="edit_unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="edit_unit" name="unit" placeholder="pcs, sets, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_asset_value" class="form-label">Asset Value (₱)</label>
                                <input type="number" class="form-control" id="edit_asset_value" name="asset_value" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_asset_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_asset_status" name="asset_status" required>
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_date_acquired" class="form-label">Date Acquired</label>
                                <input type="date" class="form-control" id="edit_date_acquired" name="date_acquired" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location" placeholder="Building, Room, etc.">
                        </div>
                        <div class="mb-3">
                            <label for="edit_asset_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_asset_description" name="asset_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_asset" class="btn btn-primary">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
