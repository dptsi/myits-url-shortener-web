<table id="{{$table_id}}" class="table table-hover" style="width:100%">
    <thead>
        <tr>
            <th class="col-long-url">URL Tujuan</th>
            <th>Kunjungan</th>
            <th class="col-short-url">Tautan Pendek</th>
            <th>Dibuat</th>
            <th class="col-qr">QR Code</th>
            @if ($table_id == "admin_links_table")
            {{-- Show action buttons only if admin view --}}
            <th>Pembuat</th>
            <th class="col-actions">Aksi</th>
            @endif
        </tr>
    </thead>
</table>
