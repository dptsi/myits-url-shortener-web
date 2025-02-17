<table id="{{$table_id}}" class="table table-hover">
    <thead>
        <tr>
            <th>Link Ending</th>
            <th>Long Link</th>
            <th>Visited</th>
            <th>Created At</th>
            <th>QRCode</th>
            @if ($table_id == "admin_links_table")
            {{-- Show action buttons only if admin view --}}
            <th>Creator</th>
            <th>Edit</th>
            <th>Disable</th>
            @endif
        </tr>
    </thead>
</table>
