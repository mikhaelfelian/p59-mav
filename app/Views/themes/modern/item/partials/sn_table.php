<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Description: Partial view for displaying SN table using DataTables
 */
?>
<div class="table-responsive">
    <table id="sn-table" class="table table-striped table-bordered" style="width:100%">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>SN</th>
                <th>Agen</th>
                <th>Terjual</th>
                <th>Status</th>
                <th>Garansi</th>
                <th>Kadaluarsa</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
(function() {
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= esc($item_id) ?>';

    // Load SN table function
    window.loadSnTable = function() {
        if (!$('#sn-table').length) return;
        
        // Destroy existing DataTable if exists
        if ($.fn.DataTable.isDataTable('#sn-table')) {
            $('#sn-table').DataTable().destroy();
        }

        $('#sn-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: baseURL + 'item-sn/getSnList',
                type: 'POST',
                data: { item_id: itemId },
                dataSrc: function(json) {
                    return json.data || [];
                }
            },
            columns: [
                { data: null, render: function(data, type, row, meta) {
                    return meta.row + 1;
                }},
                { data: 'sn' },
                { data: 'agent_name', render: function(data, type, row) {
                    return (row.agent_code || '') + ' ' + (data || '-');
                }},
                { data: 'is_sell', render: function(data) {
                    return data == '1' ? '<span class="badge bg-success">Terjual</span>' : '<span class="badge bg-secondary">Belum</span>';
                }},
                { data: 'is_activated', render: function(data) {
                    return data == '1' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-warning">Tidak Aktif</span>';
                }},
                { data: 'activated_at', render: function(data) {
                    return data ? new Date(data).toLocaleString('id-ID') : '-';
                }},
                { data: 'expired_at', render: function(data) {
                    return data ? new Date(data).toLocaleString('id-ID') : '-';
                }},
                { data: null, orderable: false, render: function(data, type, row) {
                    return '<button type="button" class="btn btn-sm btn-danger btn-delete-sn" data-id="' + row.id + '" title="Hapus">' +
                           '<i class="fas fa-trash"></i>' +
                           '</button>';
                }}
            ],
            language: {
                processing: 'Memuat...',
                emptyTable: 'Belum ada data SN',
                zeroRecords: 'Data tidak ditemukan'
            },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });
    };

    // Initial load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadSnTable, 500);
        });
    } else {
        setTimeout(loadSnTable, 500);
    }

    // Delete SN handler (using event delegation)
    $(document).on('click', '.btn-delete-sn', function() {
        const snId = $(this).data('id');
        if (!snId) return;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "SN yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteSn(snId);
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus SN ini?')) {
                deleteSn(snId);
            }
        }
    });

    function deleteSn(id) {
        fetch(baseURL + 'item-sn/delete/' + id, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        iconColor: 'white',
                        customClass: {
                            popup: 'bg-success text-light toast p-2'
                        }
                    });
                    Toast.fire({
                        html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + res.message + '</div>'
                    });
                } else {
                    alert(res.message);
                }
                
                // Reload table
                if (typeof loadSnTable === 'function') {
                    loadSnTable();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message || 'Gagal menghapus SN'
                    });
                } else {
                    alert(res.message || 'Gagal menghapus SN');
                }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + err.message
                });
            } else {
                alert('Error: ' + err.message);
            }
        });
    }
})();
</script>
