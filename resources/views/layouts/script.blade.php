<script>
    $.fn.bootstrapTable.locales['ru-RU'] = $.fn.bootstrapTable.locales['ru'] = {
        formatRecordsPerPage (pageNumber) {
            return `${pageNumber} {{ __('app.table.rows_count') }}`
        },
        formatShowingRows (pageFrom, pageTo, totalRows, totalNotFiltered) {
            return `{{ __('app.table.items') }} ${pageFrom} {{ __('app.table.po') }} ${pageTo} {{ __('app.table.iz') }} ${totalRows}`
        },
        formatSearch () {
            return '{{ __('app.common.search') }}'
        },
        formatNoMatches () {
            return '{{ __('app.table.nothing_found') }}'
        },
    }

    $.extend($.fn.bootstrapTable.defaults, $.fn.bootstrapTable.locales['ru-RU'])

    function customSort(sortName, sortOrder, data) {
        let order = sortOrder === 'desc' ? -1 : 1;

        data.sort(function (a, b) {
            if (@yield('table_extracted_dates_columns', '[]').includes(sortName)) {
                let dateAa = (a[sortName]).match(/\d\d\d\d-\d\d-\d\d/m);
                let dateBb = (b[sortName]).match(/\d\d\d\d-\d\d-\d\d/m);
                let aa = new Date(dateAa);
                let bb = new Date(dateBb);

                return (aa - bb) * order;
            } else {
                let aa = @yield('table_extracted_columns', '[]').includes(sortName) ? (a[sortName]).match(/">[^<]+<\//gm) : +((a[sortName] + '').replace(/[^\d]/g, ''));
                let bb = @yield('table_extracted_columns', '[]').includes(sortName) ? (b[sortName]).match(/">[^<]+<\//gm) : +((b[sortName] + '').replace(/[^\d]/g, ''));

                if (aa < bb) {
                    return order * -1
                }
                if (aa > bb) {
                    return order
                }
                return 0
            }
        })
    }
</script>
