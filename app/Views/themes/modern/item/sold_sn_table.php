<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-01-XX
 * Description: View for displaying sold Serial Numbers (SN) in read-only DataTable format
 */
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary">
        <i class="fas fa-shopping-cart me-2"></i>SN Terjual
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Tabel ini menampilkan semua Serial Number (SN) yang telah terjual untuk item ini.
        </div>

        <div class="table-responsive">
            <table id="sold-sn-table" class="table table-striped table-bordered" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>SN</th>
                        <th>Barcode</th>
                        <th>Agen</th>
                        <th>Tanggal Penjualan</th>
                        <th>Customer</th>
                        <th>No. Invoice</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= esc($item_id) ?>';

    // Initialize DataTable - wait for jQuery and DataTables to be available
    window.initSoldSnDataTable = function() {
        if (typeof jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            // Wait a bit and try again if jQuery/DataTables not ready
            setTimeout(window.initSoldSnDataTable, 100);
            return;
        }

        if (!$('#sold-sn-table').length) return;
        
        // Destroy existing DataTable if exists
        if ($.fn.DataTable.isDataTable('#sold-sn-table')) {
            $('#sold-sn-table').DataTable().destroy();
        }

        $('#sold-sn-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: baseURL + 'item-sn/getSoldSnList/' + itemId,
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {
                    // Add any additional data if needed
                    return d;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        thrown: thrown,
                        responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : 'No response text',
                        responseHeaders: xhr.getAllResponseHeaders()
                    });
                    
                    // Try to parse response if it looks like JSON
                    let errorMsg = 'Gagal memuat data';
                    if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMsg = response.error;
                            } else if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch(e) {
                            // Not JSON, might be HTML error
                            if (xhr.responseText.includes('error') || xhr.responseText.includes('Error')) {
                                errorMsg = 'Terjadi kesalahan pada server';
                            }
                        }
                    }
                    
                    // Show user-friendly error
                    alert('Error: ' + errorMsg + '\nStatus: ' + xhr.status);
                }
            },
            columns: [
                { 
                    data: null, 
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'sn', name: 'sn' },
                { 
                    data: 'barcode', 
                    name: 'barcode',
                    render: function(data, type, row) {
                        return data && data !== '-' ? data : '<span class="text-muted">-</span>';
                    }
                },
                { 
                    data: 'agent_name', 
                    name: 'agent_name',
                    render: function(data, type, row) {
                        return data || '<span class="text-muted">-</span>';
                    }
                },
                { 
                    data: 'sale_date', 
                    name: 'sale_date',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                },
                { 
                    data: 'customer_info', 
                    name: 'customer_info',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        return data;
                    }
                },
                { 
                    data: 'invoice_no', 
                    name: 'invoice_no',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        return '<code>' + data + '</code>';
                    }
                }
            ],
            order: [[4, 'desc']], // Order by sale date descending
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat data...',
                emptyTable: 'Tidak ada SN terjual untuk item ini',
                zeroRecords: 'Tidak ada data yang sesuai dengan filter',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data per halaman',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya'
                }
            },
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                // Any additional callback logic if needed
            }
        });
    };

    // Initialize when script loads (for AJAX-loaded content)
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        // jQuery and DataTables are ready, initialize immediately
        setTimeout(window.initSoldSnDataTable, 100);
    } else {
        // Wait for jQuery/DataTables to load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(window.initSoldSnDataTable, 100);
            });
        } else {
            // DOM already loaded, but wait for jQuery/DataTables
            setTimeout(window.initSoldSnDataTable, 100);
        }
    }
})();
</script>

