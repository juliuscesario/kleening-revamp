// resources/js/pages/machines.js

$(function() {
    const ajaxUrl = $('#machines-table').data('url');
    const apiUrl = $('#machines-table').data('api-url');

    const modalElement = document.getElementById('modal-machine');
    const modalInstance = new bootstrap.Modal(modalElement);

    const table = $('#machines-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl,
            data: function(d) {
                d.category_id = $('#filter-category').val();
                d.area_id = $('#filter-area').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'category_name', name: 'category.name' },
            { data: 'area_name', name: 'area.name' },
            { data: 'status', name: 'status' },
            { data: 'paired_machine', name: 'paired_machine_id', orderable: false },
            { data: 'notes', name: 'notes', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Filter change handlers
    $('#filter-category, #filter-area, #filter-status').on('change', function() {
        table.ajax.reload();
    });

    $('#add-machine-button').on('click', function() {
        $('#machine-form').trigger("reset");
        $('#machine-id').val('');
        $('#modal-title').html("Tambah Mesin");
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#paired-machine-wrapper').hide();
        $('#machine-paired-machine-id').html('<option value="">Tidak ada</option>');
        modalInstance.show();
    });

    // When category changes, update paired machine dropdown
    $('#machine-category-id').on('change', function() {
        const categoryId = $(this).val();
        const areaId = $('#machine-area-id').val();

        // Reset paired machine dropdown
        $('#machine-paired-machine-id').html('<option value="">Tidak ada</option>');

        if (!categoryId || !areaId) {
            $('#paired-machine-wrapper').hide();
            return;
        }

        // Determine if we need HV or Steam machines for pairing
        $.get(`${apiUrl}`, { category_id: categoryId, area_id: areaId }, function(data) {
            const category = data.find(m => m.id == categoryId)?.category;
            let targetCategorySlug = null;

            if (category && category.slug === 'hydrovacuum') {
                targetCategorySlug = 'steam';
            } else if (category && category.slug === 'steam') {
                targetCategorySlug = 'hydrovacuum';
            }

            if (targetCategorySlug) {
                // Fetch machines from the paired category in the same area
                $.get(`${apiUrl}`, { area_id: areaId }, function(allMachines) {
                    const pairedMachines = allMachines.filter(m => m.category && m.category.slug === targetCategorySlug);

                    if (pairedMachines.length > 0) {
                        $('#paired-machine-wrapper').show();
                        let options = '<option value="">Tidak ada</option>';
                        pairedMachines.forEach(m => {
                            options += `<option value="${m.id}">${m.code}${m.name ? ' — ' + m.name : ''}</option>`;
                        });
                        $('#machine-paired-machine-id').html(options);
                    } else {
                        $('#paired-machine-wrapper').hide();
                    }
                });
            } else {
                $('#paired-machine-wrapper').hide();
            }
        });
    });

    // When area changes, update paired machine dropdown
    $('#machine-area-id').on('change', function() {
        const categoryId = $('#machine-category-id').val();
        if (categoryId) {
            $('#machine-category-id').trigger('change');
        }
    });

    // Suggest code button
    $('#suggest-code-btn').on('click', function() {
        const categoryId = $('#machine-category-id').val();
        const areaId = $('#machine-area-id').val();

        if (!categoryId || !areaId) {
            Swal.fire('Perhatian', 'Pilih kategori dan area terlebih dahulu.', 'warning');
            return;
        }

        $.get(`/api/machines/next-code`, { category_id: categoryId, area_id: areaId }, function(data) {
            if (data.suggested_code) {
                $('#machine-code').val(data.suggested_code);
            } else {
                Swal.fire('Error', 'Gagal menghasilkan kode. Pastikan kategori dan area sudah dipilih.', 'error');
            }
        });
    });

    // Edit machine
    $('body').on('click', '.editMachine', function() {
        const machine_id = $(this).data('id');
        $.get(`${apiUrl}/${machine_id}`, function(data) {
            $('#modal-title').html("Edit Mesin");
            $('#machine-id').val(data.id);
            $('#machine-category-id').val(data.category_id);
            $('#machine-area-id').val(data.area_id);
            $('#machine-code').val(data.code);
            $('#machine-name').val(data.name || '');
            $('#machine-status').val(data.status);
            $('#machine-notes').val(data.notes || '');

            // Load paired machine options
            $('#machine-paired-machine-id').html('<option value="">Tidak ada</option>');

            const categoryId = data.category_id;
            const areaId = data.area_id;
            let targetCategorySlug = null;

            if (data.category && data.category.slug === 'hydrovacuum') {
                targetCategorySlug = 'steam';
            } else if (data.category && data.category.slug === 'steam') {
                targetCategorySlug = 'hydrovacuum';
            }

            if (targetCategorySlug) {
                $.get(`${apiUrl}`, { area_id: areaId }, function(allMachines) {
                    const pairedMachines = allMachines.filter(m => m.category && m.category.slug === targetCategorySlug);

                    if (pairedMachines.length > 0) {
                        $('#paired-machine-wrapper').show();
                        let options = '<option value="">Tidak ada</option>';
                        pairedMachines.forEach(m => {
                            options += `<option value="${m.id}">${m.code}${m.name ? ' — ' + m.name : ''}</option>`;
                        });
                        $('#machine-paired-machine-id').html(options);
                        $('#machine-paired-machine-id').val(data.paired_machine_id || '');
                    } else {
                        $('#paired-machine-wrapper').hide();
                    }
                });
            } else {
                $('#paired-machine-wrapper').hide();
            }

            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            modalInstance.show();
        });
    });

    // Submit form
    $('#machine-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const machine_id = $('#machine-id').val();
        const url = machine_id ? `${apiUrl}/${machine_id}` : apiUrl;
        const method = machine_id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function() {
                modalInstance.hide();
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data mesin berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function(jqXHR) {
                if (jqXHR.status === 403) {
                    modalInstance.hide();
                    Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menyimpan data ini.', 'error');
                } else if (jqXHR.status === 422) {
                    const errors = jqXHR.responseJSON.errors;
                    const fieldMap = {
                        'code': 'code',
                        'name': 'name',
                        'category_id': 'category_id',
                        'area_id': 'area_id',
                        'status': 'status',
                        'paired_machine_id': 'paired_machine_id',
                        'notes': 'notes'
                    };
                    for (const [field, errorKey] of Object.entries(fieldMap)) {
                        if (errors[field]) {
                            $(`#machine-${errorKey.replace('_', '-')}`).addClass('is-invalid');
                            $(`#${errorKey}-error`).text(errors[field][0]);
                        }
                    }
                } else {
                    modalInstance.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });

    // Delete machine
    $('body').on('click', '.deleteMachine', function() {
        const machine_id = $(this).data("id");
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data ini tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: `${apiUrl}/${machine_id}`,
                    success: function() {
                        table.ajax.reload();
                        Swal.fire('Dihapus!', 'Data mesin berhasil dihapus.', 'success');
                    },
                    error: function(jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menghapus data ini.', 'error');
                        } else {
                            Swal.fire('Gagal!', jqXHR.responseJSON.message || 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    }
                });
            }
        });
    });
});
