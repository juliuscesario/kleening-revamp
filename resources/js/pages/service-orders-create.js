document.addEventListener('DOMContentLoaded', function () {
    const stepper = new bootstrap.Tab(document.querySelector('#stepper a[data-bs-toggle="tab"]'));

    const customerSelect = $('#customer-select');
    const addressSelect = $('#address-select');
    const areaName = $('#area-name');
    const areaId = $('#area-id');
    const servicesSelect = $('#services-select');
    const staffSelect = $('#staff-select');

    // Step 1
    customerSelect.select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '/data/customers',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            },
            cache: true
        }
    });

    document.getElementById('next-to-step-2').addEventListener('click', function () {
        if (customerSelect.val()) {
            const customerId = customerSelect.val();
            addressSelect.val(null).trigger('change');
            addressSelect.select2({
                theme: 'bootstrap-5',
                ajax: {
                    url: '/data/customers/' + customerId + '/addresses',
                    dataType: 'json',
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.full_address,
                                    id: item.id,
                                    area: item.area
                                }
                            })
                        };
                    }
                }
            });

            $('a[href="#step-2"]').tab('show');
        } else {
            alert('Pilih customer terlebih dahulu.');
        }
    });

    // Step 2
    document.getElementById('back-to-step-1').addEventListener('click', function () {
        $('a[href="#step-1"]').tab('show');
    });

    document.getElementById('next-to-step-3').addEventListener('click', function () {
        // validation
        if ($('#create-so-form')[0].checkValidity()) {
            // Populate confirmation details
            let details = `
                <p><strong>Customer:</strong> ${customerSelect.select2('data')[0].text}</p>
                <p><strong>Alamat:</strong> ${addressSelect.select2('data')[0].text}</p>
                <p><strong>Area:</strong> ${areaName.val()}</p>
                <p><strong>Tanggal Pengerjaan:</strong> ${$('input[name="work_date"]').val()}</p>
                <p><strong>Layanan:</strong></p>
                <ul>`;
            servicesSelect.select2('data').forEach(item => {
                details += `<li>${item.text}</li>`;
            });
            details += '</ul>';

            if (staffSelect.val().length > 0) {
                 details += '<p><strong>Staff:</strong></p><ul>';
                staffSelect.select2('data').forEach(item => {
                    details += `<li>${item.text}</li>`;
                });
                details += '</ul>';
            }

            $('#confirmation-details').html(details);
            $('a[href="#step-3"]').tab('show');
        } else {
            alert('Harap isi semua field yang wajib diisi.');
        }
    });

    addressSelect.on('select2:select', function (e) {
        const data = e.params.data;
        areaName.val(data.area.name);
        areaId.val(data.area.id);
        $('#address_id').val(data.id);

        // Fetch staff
        staffSelect.val(null).trigger('change');
        staffSelect.select2({
            theme: 'bootstrap-5',
            ajax: {
                url: '/data/staff/by-area/' + data.area.id,
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                }
            }
        });
    });

    servicesSelect.select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '/data/services',
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: `${item.name} (Rp ${item.price})`,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    // Step 3
    document.getElementById('back-to-step-2').addEventListener('click', function () {
        $('a[href="#step-2"]').tab('show');
    });
});