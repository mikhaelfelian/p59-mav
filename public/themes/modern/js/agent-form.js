// Agent DataTable and form handling JavaScript

$(document).ready(function () {
    // Initialize DataTables if table-result exists (for agent-result.php)
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
            window.location.href = base_url + 'agent/edit?id=' + id;
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

        // Handle status toggle switch
        $(document).on('change', '.switch', function() {
            var id = $(this).data('module-id');
            var status = $(this).is(':checked') ? '1' : '0';
            var $switch = $(this);
            
            // Disable switch while processing
            $switch.prop('disabled', true);
            
            $.ajax({
                url: base_url + 'agent/toggleStatus',
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

    // Fungsi untuk menampilkan detail agen
    function showDetail(id) {
        var current_url = base_url + 'agent';
        var $bootbox = bootbox.dialog({
            title: 'Detail Agen',
            message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
            buttons: {
                success: {
                    label: 'Tutup',
                    className: 'btn-secondary'
                }
            }
        });
        $bootbox.find('.modal-dialog').css('max-width', '800px');
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

    // // Fungsi untuk menampilkan form tambah/edit di modal
    // function showForm(type = 'add', id = '') {
    //     var current_url = base_url + 'agent';
    //     var $bootbox = bootbox.dialog({
    //         title: type == 'add' ? 'Tambah Agen' : 'Edit Agen',
    //         message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
    //         buttons: {
    //             cancel: {
    //                 label: 'Batal'
    //             },
    //             success: {
    //                 label: 'Simpan',
    //                 className: 'btn-success submit',
    //                 callback: function() {
    //                     $bootbox.find('.alert').remove();
    //                     var $button_submit = $bootbox.find('button.submit');
    //                     var $button = $bootbox.find('button');
    //                     $button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
    //                     $button.prop('disabled', true);
                        
    //                     var form = $bootbox.find('form')[0];
    //                     $.ajax({
    //                         type: 'POST',
    //                         url: current_url + '/store',
    //                         data: new FormData(form),
    //                         processData: false,
    //                         contentType: false,
    //                         dataType: 'json',
    //                         success: function (data) {
    //                             $bootbox.modal('hide');
    //                             if (data.status == 'success') {
    //                                 const Toast = Swal.mixin({
    //                                     toast: true,
    //                                     position: 'top-end',
    //                                     showConfirmButton: false,
    //                                     timer: 2500,
    //                                     timerProgressBar: true,
    //                                     iconColor: 'white',
    //                                     customClass: {
    //                                         popup: 'bg-success text-light toast p-2'
    //                                     },
    //                                     didOpen: (toast) => {
    //                                         toast.addEventListener('mouseenter', Swal.stopTimer)
    //                                         toast.addEventListener('mouseleave', Swal.resumeTimer)
    //                                     }
    //                                 })
    //                                 Toast.fire({
    //                                     html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + data.message + '</div>'
    //                                 })
    //                                 $('#table-result').DataTable().ajax.reload();
    //                             } else {
    //                                 Swal.fire({
    //                                     icon: 'error',
    //                                     title: 'Error!',
    //                                     text: data.message
    //                                 });
    //                             }
    //                         },
    //                         error: function (xhr) {
    //                             Swal.fire({
    //                                 icon: 'error',
    //                                 title: 'Error!',
    //                                 text: 'Terjadi kesalahan saat memproses permintaan Anda.'
    //                             });
    //                             console.log(xhr.responseText);
    //                         }
    //                     })
    //                     return false;
    //                 }
    //             }
    //         }
    //     });
    //     $bootbox.find('.modal-dialog').css('max-width', '800px');
    //     var $button = $bootbox.find('button').prop('disabled', true);
    //     var $button_submit = $bootbox.find('button.submit');
        
    //     var formUrl = current_url + '/' + (type == 'add' ? 'add' : 'edit');
    //     if (id) {
    //         formUrl += '?id=' + id;
    //     }
        
    //     $.get(formUrl, function(html){
    //         $button.prop('disabled', false);
    //         $bootbox.find('.modal-body').empty().append(html);
    //     });
    // }

    // Fungsi untuk menghapus data agen
    function deleteItem(id) {
        $.ajax({
            url: base_url + 'agent/delete',
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
});
