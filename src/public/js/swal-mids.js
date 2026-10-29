/* Tombol SweetAlert memakai gaya tombol myITS Design System */
if (window.Swal) {
    window.Swal = Swal.mixin({
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-primary mg-x-5',
            cancelButton: 'btn btn-outline-secondary mg-x-5'
        }
    });
}
