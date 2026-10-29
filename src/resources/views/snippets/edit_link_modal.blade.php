<div id="editModal" class="modal fade effect-scale" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content bd-0">
            <form id="editLinkForm">
                <div class="modal-header bd-0 d-flex align-items-center pd-b-0">
                    <h5 class="modal-title" id="editModalLabel">Ubah Tautan</h5>
                    <button type="button" class="btn btn-icon btn-sm tx-figtree tx-medium rounded-circle d-flex align-items-center bd-0" data-dismiss="modal"><span class="ms-rounded tx-22" aria-label="close">close</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_short_url">
                    <div class="form-group mg-b-0">
                        <label for="edit_long_url" class="tx-medium">URL tujuan baru</label>
                        <input type="url" id="edit_long_url" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
