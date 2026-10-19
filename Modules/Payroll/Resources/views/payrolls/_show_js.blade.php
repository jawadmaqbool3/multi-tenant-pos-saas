<script>
    $(document).ready(function() {
        var allowanceCatalog = JSON.parse(document.getElementById('allowance_catalog').textContent || '[]');
        var deductionCatalog = JSON.parse(document.getElementById('deduction_catalog').textContent || '[]');

        document.addEventListener("table_reload", function() {
            window.location.reload();
        });

        function buildSelect(catalog, field, selected) {
            var select = $('<select>').addClass('form-select')
                .attr('name', field)
                .append($('<option>').val('').text('Select'));
            catalog.forEach(function(item) {
                var option = $('<option>').val(item.uid).text(item.name);
                if (item.uid === selected) {
                    option.attr('selected', 'selected');
                }
                select.append(option);
            });
            return select;
        }

        function buildRow(type, item) {
            var isAllowance = type === 'allowance';
            var group = isAllowance ? 'allowances' : 'deductions';
            var key = isAllowance ? 'allowance' : 'deduction';
            var catalog = isAllowance ? allowanceCatalog : deductionCatalog;

            var row = $('<div>').addClass('row ' + type + '-row mb-3');

            var selectCol = $('<div>').addClass('col-md-7')
                .append(buildSelect(catalog, group + '[__i__][' + key + ']', item ? item[key] : ''));
            var amountCol = $('<div>').addClass('col-md-4')
                .append($('<input>').attr({
                    type: 'number',
                    step: 'any',
                    min: '0',
                    placeholder: 'Amount',
                    name: group + '[__i__][amount]',
                    value: item ? item.amount : ''
                }).addClass('form-control'));
            var removeCol = $('<div>').addClass('col-md-1 d-flex align-items-center')
                .append($('<button>').attr('type', 'button').addClass('btn btn-icon btn-light-danger remove-row')
                    .html(@json('<span class="svg-icon svg-icon-3">'.\App\Support\Icon::svg('x', 16).'</span>')));

            row.append(selectCol, amountCol, removeCol);
            return row;
        }

        function reindex(container, group) {
            container.find('.' + group + '-row').each(function(i) {
                $(this).find('select').attr('name', group + '[' + i + '][' + (group === 'allowances' ? 'allowance' : 'deduction') + ']');
                $(this).find('input[type="number"]').attr('name', group + '[' + i + '][amount]');
            });
        }

        function renderRows(container, items, type) {
            container.empty();
            (items || []).forEach(function(item) {
                container.append(buildRow(type, item));
            });
        }

        $(document).on('click', '.edit-payslip', function() {
            var btn = $(this);
            var allowances = [];
            var deductions = [];
            try {
                allowances = JSON.parse(btn.attr('data-allowances') || '[]');
            } catch (e) {}
            try {
                deductions = JSON.parse(btn.attr('data-deductions') || '[]');
            } catch (e) {}

            $('#payslip_form').attr('action', btn.data('url'));
            $('#payslip_modal_title').text('Edit Payslip — ' + (btn.data('employee') || ''));
            $('#payslip_notes').val(btn.data('notes') || '');
            renderRows($('#allowance_rows'), allowances, 'allowance');
            renderRows($('#deduction_rows'), deductions, 'deduction');
            reindex($('#allowance_rows'), 'allowances');
            reindex($('#deduction_rows'), 'deductions');
        });

        $(document).on('click', '#add_allowance', function() {
            $('#allowance_rows').append(buildRow('allowance', null));
            reindex($('#allowance_rows'), 'allowances');
        });

        $(document).on('click', '#add_deduction', function() {
            $('#deduction_rows').append(buildRow('deduction', null));
            reindex($('#deduction_rows'), 'deductions');
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('.row').remove();
            reindex($('#allowance_rows'), 'allowances');
            reindex($('#deduction_rows'), 'deductions');
        });
    });
</script>
