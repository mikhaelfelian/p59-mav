// Define required variables for TinyMCE and DataTables

$(document).ready(function() {
    // Initialize DataTables if table-result exists (for item-result.php)
    if ($('#table-result').length) {
        var column = JSON.parse($('#dataTables-column').text());
        var settings = JSON.parse($('#dataTables-setting').text());
        var url = $('#dataTables-url').text();

        $('#table-result').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": url,
                "type": "POST"
            },
            "columns": column,
            "order": settings.order,
            "columnDefs": settings.columnDefs,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "language": {
                "processing": "Memuat...",
                "emptyTable": "Tidak ada data",
                "zeroRecords": "Data tidak ditemukan"
            }
        });

        // Event tombol tambah data - redirect to add page
        $('.btn-add').on('click', function (e) {
            // Let the link work normally - no preventDefault
        });

        // Event tombol detail
        $(document).on('click', '.btn-detail', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            showDetail(id);
        });

        // Event tombol edit - redirect to edit page
        $(document).on('click', '.btn-edit', function (e) {
            var id = $(this).data('id');
            window.location.href = base_url + 'item/edit?id=' + id;
        });

        // Event tombol hapus
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang sudah dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        });

        // Fungsi untuk menghapus data item
        function deleteItem(id) {
            $.ajax({
                url: base_url + 'item/delete',
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Dihapus!', response.message, 'success');
                        $('#table-result').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Delete Error:', xhr.responseText);
                    Swal.fire('Error!', 'Gagal menghapus data: ' + error, 'error');
                }
            });
        }

        // Handle status toggle switch
        $(document).on('change', '.switch', function() {
            var id = $(this).data('module-id');
            var status = $(this).is(':checked') ? '1' : '0';
            var $switch = $(this);
            
            // Disable switch while processing
            $switch.prop('disabled', true);
            
            $.ajax({
                url: base_url + 'item/toggleStatus',
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: { 
                    id: id, 
                    status: status 
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Show success toast
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            iconColor: 'white',
                            customClass: {
                                popup: 'bg-success text-light toast p-2'
                            }
                        });
                        Toast.fire({
                            html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Status berhasil diubah</div>'
                        });
                    } else {
                        // Revert switch state on error
                        $switch.prop('checked', !$switch.prop('checked'));
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Gagal mengubah status'
                        });
                    }
                },
                error: function (xhr) {
                    // Revert switch state on error
                    $switch.prop('checked', !$switch.prop('checked'));
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengubah status'
                    });
                },
                complete: function() {
                    // Re-enable switch
                    $switch.prop('disabled', false);
                }
            });
        });
    }
    // Price formatting
    $('.price-format').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            $(this).val(parseInt(value).toLocaleString('id-ID'));
        }
    });
    
    // Add specification row
    $(document).on('click', '#add-spec', function() {
        var specOptions = window.specOptions || {};
        var newRow = '<div class="row mb-2 spec-row">' +
            '<div class="col-md-5">' +
            '<select name="spec_name[]" class="form-control spec-select">' +
            '<option value="">Pilih Spesifikasi</option>';
        
        for (var id in specOptions) {
            newRow += '<option value="' + id + '">' + specOptions[id] + '</option>';
        }
        
        newRow += '</select></div>' +
            '<div class="col-md-5">' +
            '<input type="text" name="spec_value[]" class="form-control" placeholder="Masukkan nilai spesifikasi">' +
            '</div>' +
            '<div class="col-md-2">' +
            '<a href="javascript:void(0)" class="btn btn-danger btn-sm delete-spec">' +
            '<i class="fas fa-times"></i></a>' +
            '</div>' +
            '</div>';
        
        $('#specification-container').append(newRow);
    });
    
    // Remove specification row
    $(document).on('click', '.delete-spec', function() {
        $(this).closest('.spec-row').remove();
    });
    
    // Initialize select2
    $('.select2').select2();
    
    
    // Simple file input preview
    $('input[name="image"]').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.feature-image-preview').remove();
                $('<div class="feature-image-preview mt-2"><img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"/></div>').insertAfter('input[name="image"]');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Initialize TinyMCE for description with a small delay to ensure all scripts are loaded
    setTimeout(function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
            selector: '.tinymce',
            plugins: 'advlist lists link wordcount codesample',
            toolbar: 'styleselect | bold italic underline strikethrough | forecolor | numlist bullist | codesample',
            branding: false,
            statusbar: false,
            
            
            codesample_content_css: base_url + "public/vendors/prism/themes/prism-dark.css",
        }).then(function(editors) {
            if ($('html').attr('data-bs-theme') == 'dark') {
                $iframe = $('.card-body').find('iframe');
                $iframe_content = $iframe.contents();
                $iframe_content.find('#theme-style').remove();
                $iframe_content.find("head").append('<style id="theme-style">body{color: #adb5bd}</style>');  
                $iframe_content.find("head").append('<style id="theme-style">::-webkit-scrollbar { width: 15px; height: 3px;}::-webkit-scrollbar-button {  background-color: #141925;height: 0; }::-webkit-scrollbar-track {  background-color: #646464;}::-webkit-scrollbar-track-piece { background-color: #202632;}::-webkit-scrollbar-thumb { height: 35px; background-color: #181c26;border-radius: 0;}::-webkit-scrollbar-corner { background-color: #646464;}}::-webkit-resizer { background-color: #666;}</style>');  
            }
            });
        } else {
            console.error('TinyMCE is not loaded. Please check if the script is included properly.');
        }
    }, 500);
    
    // Fungsi untuk menampilkan detail item
    function showDetail(id) {
        var current_url = base_url + 'item';
        var $bootbox = bootbox.dialog({
            title: 'Detail Item',
            message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
            buttons: {
                success: {
                    label: 'Tutup',
                    className: 'btn-secondary'
                }
            }
        });
        $bootbox.find('.modal-dialog').css('max-width', '1000px');
        var $button = $bootbox.find('button').prop('disabled', true);
        
        var detailUrl = current_url + '/detail?id=' + id;
        
        $.ajax({
            url: detailUrl,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(html){
                $button.prop('disabled', false);
                $bootbox.find('.modal-body').empty().append(html);
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false);
                console.log('Detail Error:', xhr.responseText);
                $bootbox.find('.modal-body').html('<div class="alert alert-danger">Error loading detail: ' + error + '</div>');
            }
        });
    }
});